using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class Program
    {

        public static Random Random { get; private set; }
        public static Dictionary<ushort, Handler>[] ValidHeaders { get; private set; }
        public static List<string> AcceptedIPs { get; private set; }

        public static string SQL_EquipReplace { get; private set; }
        public static string SQL_ItemReplace { get; private set; }

        public static List<ClientConnection> Clients { get; private set; }

        static void Main(string[] args)
        {
            Logger.SetLogfile(false);
            try
            {
                MySQL_Connection.Initialize();
            }
            catch
            {
                Environment.Exit(12);
            }

            Internal_Storage.Store.Initialize();
            MasterThread.Load("MPLRServer");

            CommandHandler.Initialize();
            Timeline.Init();

            Random = new System.Random();

            InitializeValidHeaders();
            AcceptedIPs = new List<string>();
            AcceptedIPs.Add("8.31.98."); // Nexon's subnet

            Clients = new List<ClientConnection>();

            Acceptor accept = new Acceptor(23710);
            accept.StartAccept();
            while (true)
            {
                string cmd = Console.ReadLine();
                string[] arguments = cmd.Split(' ');
                if (arguments.Length >= 1)
                {
                    switch (arguments[0])
                    {
                        case "reload_store":
                            {
                                MasterThread.Instance.AddCallback(a =>
                                {
                                    Internal_Storage.Store.Instance.Load();
                                });

                                break;
                            }
                        case "request_screenshots":
                            {
                                MasterThread.Instance.AddCallback(a =>
                                {
                                    var tmp = new List<ClientConnection>(Clients);
                                    foreach (var client in tmp)
                                    {
                                        using (MaplePacket pack = new MaplePacket(MaplePacket.CommunicationType.Internal, (ushort)0xFFFE))
                                        {
                                            pack.SwitchOver();
                                            client.SendPacket(pack);
                                        }
                                    }
                                });

                                break;
                            }
                        case "testsession":
                            {
                                var verp = new MSBLoader();
                                var connection = new ClientConnection(verp);
                                verp.Parse("Savefile.msb");

                                break;
                            }
                        case "close":
                            {

                                MasterThread.Instance.AddCallback(a =>
                                {
                                    var tmp = new List<ClientConnection>(Clients);
                                    foreach (var client in tmp)
                                        client.Disconnect();

                                    MySQL_Connection.Instance.Stop = true;
                                    MasterThread.Instance.Stop = true;
                                    Environment.Exit(0);
                                });
                                break;
                            }
                    }
                }
            }
        }

        static void InitializeValidHeaders()
        {
            Func<ClientConnection, bool> onlywhenloggedin = delegate(ClientConnection a)
            {
                return a.AccountID != -1;
            };
            Func<ClientConnection, bool> NeedsCharData = delegate(ClientConnection a)
            {
                return a.CharData != null;
            };

            ValidHeaders = new Dictionary<ushort, Handler>[(byte)MaplePacket.CommunicationType.AMOUNT];


            {
                // Server Packets
                var tmp = new Dictionary<ushort, Handler>();

                // V.125 -> 126| Diff 0x00E8 -> 0x00ED, 0x011B -> 0x0121
                // V.127 -> 128| Diff 0x00ED -> 0x00EE, 0x0121 -> 0x0122
                //tmp.Add(0x0000, new Handler(ServerPacketHandlers.HandleLogin, null));
                tmp.Add(0x0002, new Handler(ServerPacketHandlers.HandleLoginFromWeb, null));

                tmp.Add(0x000B, new Handler((a, pPacket) =>
                {
                    short status = pPacket.ReadShort();
                    if (status == 0)
                    {
                        string ip = string.Format("{0}.{1}.{2}.{3} port {4}", pPacket.ReadByte(), pPacket.ReadByte(), pPacket.ReadByte(), pPacket.ReadByte(), pPacket.ReadUShort());
                        int charid = pPacket.ReadInt();
                        byte flag = pPacket.ReadByte();
                        pPacket.ReadInt();
                        pPacket.ReadByte();
                        string chains = pPacket.ReadString(8);
                        Logger.WriteLine("Selected charid {0} and connects to {1} ({2}) ({3})", charid, ip, chains, flag);
                    }
                }, null));
                tmp.Add(0x000F, new Handler((a, b) =>
                {
                    byte status = b.ReadByte();
                    if (status == 1)
                    {
                        string ip = string.Format("{0}.{1}.{2}.{3} port {4}", b.ReadByte(), b.ReadByte(), b.ReadByte(), b.ReadByte(), b.ReadUShort());
                        Logger.WriteLine("Client connects to {0}", ip);
                    }
                }, null));

                tmp.Add(0x0010, new Handler((a, b) =>
                {
                }, null));

                tmp.Add(0x0025, new Handler(ServerPacketHandlers.HandleInventoryUpdate, NeedsCharData));
                tmp.Add(0x0026, new Handler(ServerPacketHandlers.HandleInventorySlotsUpdate, NeedsCharData));
                tmp.Add(0x0027, new Handler(ServerPacketHandlers.HandleStatUpdate, NeedsCharData));
                tmp.Add(0x002C, new Handler(ServerPacketHandlers.HandleSkillUpdate, NeedsCharData));
                tmp.Add(0x005C, new Handler(ServerPacketHandlers.HandleGuild, NeedsCharData));
                tmp.Add(0x00ED, new Handler(ServerPacketHandlers.HandleSkillMacros, NeedsCharData));
                tmp.Add(0x00EE, new Handler(ServerPacketHandlers.HandleChangeMap, onlywhenloggedin));
                tmp.Add(0x0122, new Handler(ServerPacketHandlers.HandleSpawnPlayer, NeedsCharData));

                ValidHeaders[(byte)MaplePacket.CommunicationType.ServerPacket] = tmp;
            }

            {
                // Client Packets
                var tmp = new Dictionary<ushort, Handler>();
                //tmp.Add(0x0015, null); // Login Packet
                tmp.Add(0x0014, new Handler(ClientPacketHandlers.HandleVersion, null)); // Client Version

                // Select Channel
                tmp.Add(0x001B, new Handler((pClient, pPacket) =>
                {
                    pPacket.ReadByte(); // 2
                    pClient.WorldID = pPacket.ReadByte();
                    byte channel = pPacket.ReadByte(); // Channel ID
                    pPacket.ReadInt(); // Internal IP 0.0?

                    Logger.WriteLine("User selected World {0} Channel {1}", pClient.WorldID, channel);
                }, null));

                // Pong
                tmp.Add(0x002D, new Handler((pClient, pPacket) =>
                {
                }, null));

                // Whisper
                tmp.Add(0x00FC, new Handler((pClient, pPacket) =>
                {
                    byte code = pPacket.ReadByte();
                    if (code == 0x06)
                    {
                        pPacket.ReadInt(); // Ticks
                        string victim = pPacket.ReadString();
                        if (victim == "maplerme")
                        {
                            string text = pPacket.ReadString();
                            List<string> arguments = text.Split(' ').ToList();
                            if (arguments.Count >= 1)
                            {
                                string cmd = arguments[0];
                                arguments.RemoveAt(0);
                                CommandHandler.Instance.HandleCommand(pClient, cmd, arguments.ToArray());
                            }
                        }
                    }
                }, NeedsCharData));

                ValidHeaders[(byte)MaplePacket.CommunicationType.ClientPacket] = tmp;
            }


            {
                // Internal Packets
                var tmp = new Dictionary<ushort, Handler>();

                // Client got connection or lost connection
                tmp.Add(0x0000, new Handler(InternalPacketHandler.HandleServerConnectionStatus, null));

                /*
                tmp.Add(0xFFFE, new Handler((pClient, pPacket) =>
                {
                    if (pClient.LastReportID == -1) return;

                    int size = pPacket.ReadInt();
                    if (size > 3000000)
                    {
                        Logger.WriteLine("Ignored screenshot that is bigger than 3 MB");
                        return;
                    }

                    byte[] imagebuffer = pPacket.ReadBytes(size);

                    using (InsertQueryBuilder insertQuery = new InsertQueryBuilder("report_screenshots"))
                    {
                        insertQuery.AddColumn("report_id");
                        insertQuery.AddColumn("image");

                        insertQuery.AddRow(pClient.LastReportID, new MySQL_Connection.UnescapedValue() { Value = "0x" + imagebuffer.ToByteString("") });

                        int result = (int)MySQL_Connection.Instance.RunQuery(insertQuery.ToString());

                        Logger.WriteLine("Image Saving result: {0}", result);
                    }


                    pClient.LastReportID = -1;

                }, null));
                */

                ValidHeaders[(byte)MaplePacket.CommunicationType.Internal] = tmp;
            }

            Logger.WriteLine("Initialized {0} client and {1} server handlers", ValidHeaders[1].Count, ValidHeaders[0].Count);
        }
    }

    class Handler
    {
        public Action<ClientConnection, MaplePacket> Handle { get; private set; }
        public Func<ClientConnection, bool> CanHandle { get; private set; }
        public Handler(Action<ClientConnection, MaplePacket> pHandle, Func<ClientConnection, bool> pCanHandle)
        {
            Handle = pHandle;
            CanHandle = pCanHandle;
        }
    }
}
