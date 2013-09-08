using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
   public class Program
    {

        public static Random Random { get; private set; }
        public static Dictionary<ushort, Handler>[] ValidHeaders { get; private set; }
        public static List<string> AcceptedIPs { get; private set; }

        public static string SQL_EquipReplace { get; private set; }
        public static string SQL_ItemReplace { get; private set; }

        public static List<ClientConnection> Clients { get; private set; }

        // lolname
        static void UnexpectedExHandler(object sender, UnhandledExceptionEventArgs args)
        {
            Exception e = (Exception)args.ExceptionObject;
            System.IO.File.AppendAllText("RUNTIME_ERROR.txt", e.ToString());
        }


        static void Main(string[] args)
        {
            AppDomain currentDomain = AppDomain.CurrentDomain;
            currentDomain.UnhandledException += new UnhandledExceptionEventHandler(UnexpectedExHandler);
            Console.CancelKeyPress += Console_CancelKeyPress;

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

            AccountDataCache.Initialize();
            GMSKeys.Initialize();

            CommandHandler.Initialize();
            Timeline.Init();

            Random = new System.Random();
            {
                InitializeValidHeaders();
                AcceptedIPs = new List<string>();
                AcceptedIPs.Add("8.31.9"); // Nexon's subnet

                Clients = new List<ClientConnection>();
                StartPinger();
            }

            EXPTable.Load();

            SessionRestartCache.Start();

            // For clients
            Acceptor accept = new Acceptor(23710, sock =>
            {
                new ClientConnection(sock);
            });

            // For online check!
            byte[] OnlineCheckInfo = null;
            {
                MaplePacket packet = new MaplePacket(ClientPacketHandlers.LatestMajorVersion);
                packet.WriteByte(ClientPacketHandlers.LatestLocale);

                byte[] temp = packet.ToArray();

                OnlineCheckInfo = new byte[temp.Length + 1];
                Buffer.BlockCopy(temp, 0, OnlineCheckInfo, 1, temp.Length);
                OnlineCheckInfo[0] = (byte)(temp.Length + 4);

                packet.Dispose();
                packet = null;
            }
            Acceptor acceptCheck = new Acceptor(23711, sock =>
            {
                sock.Send(OnlineCheckInfo);
                sock.Send(BitConverter.GetBytes(Clients.Count));
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
                if (cmd == null) break; // CTRL + C
                string[] arguments = cmd.Split(' ');
                if (arguments.Length >= 1)
                {
                    switch (arguments[0])
                    {
                        case "getkeys":
                            {
                                GMSKeys.Initialize();
                                break;
                            }
                        case "reload_store":
                            {
                                MasterThread.Instance.AddCallback(a =>
                                {
                                    AccountDataCache.Instance.Load();
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
                                bool raw = arguments.Length > 1;
                                var verp = new MSBLoader();
                                var connection = new ClientConnection(verp);
                                verp.Parse("Savefile.msb", raw);

                                break;
                            }
                        case "players":
                            {
                                string names = string.Join(", ", Clients);
                                Console.WriteLine("Players online:\r\n{0}", names);
                                break;
                            }
                        case "close":
                        case "stop":
                        case "exit":
                            {

                                MasterThread.Instance.AddCallback(a =>
                                {
                                    var tmp = new List<ClientConnection>(Clients);
                                    foreach (var client in tmp)
                                    {
                                        // client.Save(true, true);
                                        client.Disconnect();
                                    }

                                    MySQL_Connection.Instance.Stop = true;
                                    MasterThread.Instance.Stop = true;
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

        static void Console_CancelKeyPress(object sender, ConsoleCancelEventArgs e)
        {
            var tmp = new List<ClientConnection>(Clients);
            foreach (var client in tmp)
            {
                client.Disconnect();
            }
        }

        static System.Threading.Timer _timert;
        static void StartPinger()
        {
            _timert = new System.Threading.Timer((obj) =>
            {
                MasterThread.Instance.AddCallback(a =>
                {
                    var tmp = new List<ClientConnection>(Clients);
                    foreach (var client in tmp)
                    {
                        if (!client.Pong)
                        {
                            client.Logger_WriteLine("Connection Timeout");
                            client.Disconnect();
                        }
                        else
                        {
                            client.Pong = false;
                            using (MaplePacket mp = new MaplePacket(MaplePacket.CommunicationType.ServerPacket, 0xEE01))
                            {
                                client.SendPacket(mp);
                            }
                        }
                    }
                });

            }, null, 0, 20000);
        }

        static void InitializeValidHeaders()
        {
            Func<ClientConnection, bool> NotMaplerLoggedin = delegate(ClientConnection a)
            {
                return a.AccountID == -1;
            };
            Func<ClientConnection, bool> IdentifiedAccountAndUser = delegate(ClientConnection a)
            {
                return !NotMaplerLoggedin(a) && a.UserID != -1;
            };
            Func<ClientConnection, bool> OnCharacterSelect = delegate(ClientConnection a)
            {
                return !NotMaplerLoggedin(a) && a.UserID != -1 && a.ChannelID != 255;
            };
            Func<ClientConnection, bool> OnLoadedCharData = delegate(ClientConnection a)
            {
                return !NotMaplerLoggedin(a) && a.CharData != null;
            };

            ValidHeaders = new Dictionary<ushort, Handler>[(byte)MaplePacket.CommunicationType.AMOUNT];


            {
                // Server Packets
                var tmp = new Dictionary<ushort, Handler>();

                // V.125 -> 126| Diff 0x00E8 -> 0x00ED, 0x011B -> 0x0121
                // V.127 -> 128| Diff 0x00ED -> 0x00EE, 0x0121 -> 0x0122
                // V.131 -> 132| Diff 0x00EE -> 0x00F4...
                // V.134 -> 135| Diff 0x00F4 -> 0x00F2... WUT
                // V.135 -> 137| Diff 0x00F2 -> 0x00FD... WUT | 0x007B -> 0x007D | 0x0059 -> 0x005A
                // V.140 -> 141| Diff 0x00FD -> 0x010C... 0x0032 -> 0x0033 | 0x0165 -> 0x0177
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
                        //string chains = pPacket.ReadString(8);
                        pConnection.Logger_WriteLine("Selected charid {0} and connects to {1} ({2})", charid, ip, flag);
                    }
                }, null));
                tmp.Add(0x000E, new Handler(ServerPacketHandlers.HandleCharacterDeletion, OnCharacterSelect));
                tmp.Add(0x0010, new Handler((pConnection, pPacket) =>
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

                tmp.Add(0x0011, new Handler((a, b) =>
                {
                    // ping
                }, null));

                tmp.Add(0x0025, new Handler(ServerPacketHandlers.HandleInventoryUpdate, OnLoadedCharData));
                tmp.Add(0x0026, new Handler(ServerPacketHandlers.HandleInventorySlotsUpdate, OnLoadedCharData));
                tmp.Add(0x0027, new Handler(ServerPacketHandlers.HandleStatUpdate, OnLoadedCharData));
                tmp.Add(0x002C, new Handler(ServerPacketHandlers.HandleSkillUpdate, OnLoadedCharData));
                tmp.Add(0x0033, new Handler(ServerPacketHandlers.HandleMessage, OnLoadedCharData));
                tmp.Add(0x005D, new Handler(ServerPacketHandlers.HandleBuddyList, OnLoadedCharData));
                tmp.Add(0x005F, new Handler(ServerPacketHandlers.HandleGuild, OnLoadedCharData));
                tmp.Add(0x0060, new Handler(ServerPacketHandlers.HandleAlliance, OnLoadedCharData));
                tmp.Add(0x0081, new Handler(ServerPacketHandlers.HandleFamiliarList, OnLoadedCharData));
                tmp.Add(0x00D2, new Handler(ServerPacketHandlers.HandleAbilityInfoUpdate, OnLoadedCharData));
                tmp.Add(0x00E6, new Handler(ServerPacketHandlers.HandleMaplePointAmount, OnLoadedCharData));
                tmp.Add(0x010B, new Handler(ServerPacketHandlers.HandleSkillMacros, OnLoadedCharData));
                tmp.Add(0x010C, new Handler(ServerPacketHandlers.HandleChangeMap, IdentifiedAccountAndUser));
                tmp.Add(0x0143, new Handler(ServerPacketHandlers.HandleSpawnPlayer, OnLoadedCharData));
                tmp.Add(0x0177, new Handler(ServerPacketHandlers.HandleSpawnAndroid, OnLoadedCharData));
                //tmp.Add(0x02B1, new Handler(ServerPacketHandlers.HandleTradeData, NeedsCharData));

                tmp.Add(0x030E, new Handler(ServerPacketHandlers.HandleKeymap, OnLoadedCharData));
                // Testing more data throughput
                //tmp.Add(530, null);
                //tmp.Add(435, null);
                //tmp.Add(569, null);
                //tmp.Add(566, null);
                //tmp.Add(567, null);

                ValidHeaders[(byte)MaplePacket.CommunicationType.ServerPacket] = tmp;
            }

            {
                // V.140 -> 141: 0x0014 -> 0x003F, Pong: 0x002D -> 0x0046
                // Client Packets
                var tmp = new Dictionary<ushort, Handler>();
                //tmp.Add(0x0015, null); // Login Packet
                tmp.Add(0x003F, new Handler(ClientPacketHandlers.HandleVersion, null)); // Client Version

                // Select Channel
                tmp.Add(0x0043, new Handler((pConnection, pPacket) =>
                {
                    byte requestType = pPacket.ReadByte();
                    if (requestType != 2)
                    {
                        pConnection.Logger_WriteLine("Used weblogin, probably!");
                        // Logging in via web.... D:
                        pPacket.ReadString(); // Login key
                        pPacket.Skip(16); // CC key
                        pPacket.Skip(4); // Unknown, 0? Prolly login mode
                        pPacket.Skip(1); // ...?
                    }
                    pConnection.WorldID = pPacket.ReadByte();
                    pConnection.ChannelID = pPacket.ReadByte(); // Channel ID
                    pPacket.ReadInt(); // Client LAN IP -.-: 192.168.0.212

                    pConnection.Logger_WriteLine("User selected World {0} Channel {1}", pConnection.WorldID, pConnection.ChannelID);
                }, null));

                tmp.Add(0x0027, new Handler(ClientPacketHandlers.HandleCharacterLoadRequest, null));

                // Pong
                tmp.Add(0x0046, new Handler((pConnection, pPacket) =>
                {
                }, null));

                tmp.Add(0x0052, new Handler((pConnection, pPacket) =>
                {
                    byte new_channel = pPacket.ReadByte();
                    pConnection.Logger_WriteLine("Requesting CC to channel {0}", new_channel);
                }, null));

                // Whisper
                tmp.Add(0xFFFC, new Handler((pConnection, pPacket) =>
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
                }, OnLoadedCharData));


                tmp.Add(0x0134, new Handler(ClientPacketHandlers.HandleKeymapUpdate, OnLoadedCharData));



                // Internal packets

                tmp.Add(0xEE00, new Handler(InternalPacketHandler.HandleServerConnectionStatus, null));
                tmp.Add(0xEE01, new Handler((a, b) =>
                {
                    a.Pong = true;
                }, null));
                /*
                tmp.Add(0xEE02, new Handler((a, b) =>
                {
                    // Create screenshot with all character names
                    MySQL_Connection.Instance.RunQuery("INSERT INTO reports VALUES " +
                        MySQL_Connection.BuildValuesRow(null, string.Join(";", a._CharactersInMap), a.CharacterInternalID, MySQL_Connection.NOW, a.CharData.Stats.MapID, null));

                    using (MaplePacket packet = new MaplePacket(MaplePacket.CommunicationType.ServerPacket, 0xEEFE))
                    {
                        packet.WriteString("http://i.mapler.me/reports/upload.php");
                        packet.WriteString(MySQL_Connection.Instance.GetLastInsertId().ToString());
                        a.SendPacket(packet);
                    }

                }, OnLoadedCharData));*/
                tmp.Add(0xEE03, new Handler(InternalPacketHandler.HandleTokenCheck, NotMaplerLoggedin));

                ValidHeaders[(byte)MaplePacket.CommunicationType.ClientPacket] = tmp;
            }

            Logger.WriteLine("Initialized {0} client and {1} server handlers", ValidHeaders[1].Count, ValidHeaders[0].Count);
        }
    }

    public class Handler
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
