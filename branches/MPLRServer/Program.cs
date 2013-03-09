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

            MasterThread.Load("MPLRServer");

            try
            {
                MySQL_Connection.Initialize();
            }
            catch
            {
                Environment.Exit(12);
            }

            Internal_Storage.Store.Initialize();

            CommandHandler.Initialize();
            Timeline.Init();

            Random = new System.Random();

            InitializeValidHeaders();
            AcceptedIPs = new List<string>();
            AcceptedIPs.Add("8.31.98."); // Nexon's subnet

            Clients = new List<ClientConnection>();

            // For clients
            Acceptor accept = new Acceptor(23710, sock =>
            {
                new ClientConnection(sock);
            });

            // For online check!
            Acceptor acceptCheck = new Acceptor(23711, sock =>
            {
                sock.Shutdown(System.Net.Sockets.SocketShutdown.Both);
                sock.Close();
            });


            Logger.WriteLine("+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+");
            Logger.WriteLine("|                                             |");
            Logger.WriteLine("|              Mapler.me Server               |");
            Logger.WriteLine("|                                             |");
            Logger.WriteLine("+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+");
            Logger.WriteLine("|           Build For: {0,3} Locale {1,1}           |", ClientPacketHandlers.LatestMajorVersion, ClientPacketHandlers.LatestLocale);
            Logger.WriteLine("+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+");


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
                                        using (MaplePacket pack = new MaplePacket(MaplePacket.CommunicationType.ServerPacket, 0xEEFE))
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
                        default:
                            Console.WriteLine("Command not found");
                            break;
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

                tmp.Add(0x000B, new Handler((pConnection, pPacket) =>
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
                        pConnection.Logger_WriteLine("Selected charid {0} and connects to {1} ({2}) ({3})", charid, ip, chains, flag);
                    }
                }, null));
                tmp.Add(0x000F, new Handler((pConnection, pPacket) =>
                {
                    byte status = pPacket.ReadByte();
                    if (status == 1)
                    {
                        string ip = string.Format("{0}.{1}.{2}.{3} port {4}", 
                            pPacket.ReadByte(), pPacket.ReadByte(), pPacket.ReadByte(), pPacket.ReadByte(), 
                            pPacket.ReadUShort());
                        pConnection.Logger_WriteLine("Client connects to {0}", ip);
                    }
                }, null));

                tmp.Add(0x0010, new Handler((a, b) =>
                {
                }, null));

                tmp.Add(0x0025, new Handler(ServerPacketHandlers.HandleInventoryUpdate, NeedsCharData));
                tmp.Add(0x0026, new Handler(ServerPacketHandlers.HandleInventorySlotsUpdate, NeedsCharData));
                tmp.Add(0x0027, new Handler(ServerPacketHandlers.HandleStatUpdate, NeedsCharData));
                tmp.Add(0x002C, new Handler(ServerPacketHandlers.HandleSkillUpdate, NeedsCharData));
                tmp.Add(0x005D, new Handler(ServerPacketHandlers.HandleGuild, NeedsCharData));
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
                tmp.Add(0x001B, new Handler((pConnection, pPacket) =>
                {
                    byte requestType = pPacket.ReadByte();
                    if (requestType == 1)
                    {
                        // Logging in via web.... D:
                        pPacket.ReadString(); // Login key
                        pPacket.Skip(16); // CC key
                        pPacket.Skip(4); // Unknown, 0? Prolly login mode
                        pPacket.Skip(1); // ...?
                    }
                    pConnection.WorldID = pPacket.ReadByte();
                    byte channel = pPacket.ReadByte(); // Channel ID
                    pPacket.ReadInt(); // Internal IP 0.0?

                    pConnection.Logger_WriteLine("User selected World {0} Channel {1}", pConnection.WorldID, channel);
                }, null));

                // Pong
                tmp.Add(0x002D, new Handler((pConnection, pPacket) =>
                {
                }, null));

                // Whisper
                tmp.Add(0x00FC, new Handler((pConnection, pPacket) =>
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
                                CommandHandler.Instance.HandleCommand(pConnection, cmd, arguments.ToArray());
                            }
                        }
                    }
                }, NeedsCharData));

                // Internal packets

                tmp.Add(0xEE00, new Handler(InternalPacketHandler.HandleServerConnectionStatus, null));

                ValidHeaders[(byte)MaplePacket.CommunicationType.ClientPacket] = tmp;
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
