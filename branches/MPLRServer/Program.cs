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
#if LOCALE_GMS
            GMSKeys.Initialize();
#endif

            CommandHandler.Initialize();
            Timeline.Init();

            Random = new System.Random();
            {
                InitializeValidHeaders();
                AcceptedIPs = new List<string>();
#if LOCALE_GMS
                AcceptedIPs.Add("8.31.9"); // GMS
#elif LOCALE_EMS
                AcceptedIPs.Add("109.234.77"); // EMS
#endif

                Clients = new List<ClientConnection>();
                StartPinger();
                StartCharacterDeleteQueue();
            }

            EXPTable.Load();

            SessionRestartCache.Start();

            // For clients
            Acceptor accept = new Acceptor(ServerMapleInfo.MAPLER_PORT, sock =>
            {
                new ClientConnection(sock);
            });

            // For online check!
            byte[] OnlineCheckInfo = null;
            {
                MaplePacket packet = new MaplePacket(ServerMapleInfo.VERSION);
                packet.WriteByte(ServerMapleInfo.LOCALE);

                byte[] temp = packet.ToArray();

                OnlineCheckInfo = new byte[temp.Length + 1];
                Buffer.BlockCopy(temp, 0, OnlineCheckInfo, 1, temp.Length);
                OnlineCheckInfo[0] = (byte)(temp.Length + 4);

                packet.Dispose();
                packet = null;
            }
            Acceptor acceptCheck = new Acceptor(ServerMapleInfo.MAPLER_PORT_SERVER_INFO, sock =>
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
#if LOCALE_GMS
            Logger.WriteLine("|                   GLOBAL                    |");
#elif LOCALE_EMS
            Logger.WriteLine("|                   EUROPE                    |");
#elif LOCALE_KMS
            Logger.WriteLine("|                   KOREA                     |");
#endif
            Logger.WriteLine("|                                             |");
            Logger.WriteLine("+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+");
            Logger.WriteLine("|           Build For: {0,3} Locale {1,1}           |", ServerMapleInfo.VERSION, ServerMapleInfo.LOCALE);
            Logger.WriteLine("+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+");
            Logger.WriteLine("Accepting connections on {0}, and info requests on {1}", ServerMapleInfo.MAPLER_PORT, ServerMapleInfo.MAPLER_PORT_SERVER_INFO);


            while (true)
            {
                string cmd = Console.ReadLine();
                if (cmd == null) break; // CTRL + C
                string[] arguments = cmd.Split(' ');
                if (arguments.Length >= 1)
                {
                    switch (arguments[0])
                    {
#if LOCALE_GMS
                        case "getkeys":
                            {
                                GMSKeys.Initialize();
                                break;
                            }
#endif
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
                                int accountid = arguments.Length > 1 ? Int32.Parse(arguments[1]) : -1;
                                bool raw = arguments.Length > 2;
                                var verp = new MSBLoader();
                                var connection = new ClientConnection(verp);
                                connection.AccountID = accountid;
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

        static System.Threading.Timer _timerCharDeleteQueue;
        static void StartCharacterDeleteQueue()
        {
            _timerCharDeleteQueue = new System.Threading.Timer((obj) =>
            {
                MasterThread.Instance.AddCallback(a =>
                {
                    // Get characters that must be deleted
                    List<int> ids;
                    using (var datareader = MySQL_Connection.Instance.RunQuery("SELECT id FROM character_delete_queue WHERE parsed_at IS NULL") as MySql.Data.MySqlClient.MySqlDataReader)
                    {
                        if (!datareader.HasRows) return;

                        ids = new List<int>();

                        while (datareader.Read()) {
                            int id = datareader.GetInt32(0);
                            if (!ids.Contains(id))
                                ids.Add(id);
                        }
                    }

                    // Remove those who are logged in...!
                    Clients.ForEach((c) =>
                    {
                        if (ids.Contains(c.CharacterInternalID))
                            ids.Remove(c.CharacterInternalID);
                    });
                    
                    // First, remove them from the cache

                    int deletes = ids.Count((id) => {
                        var info = AccountDataCache.Instance.GetCharInfoByInternalID(id);
                        if (info == null) return false; // weird...
                        AccountDataCache.Instance.KnownCharlist.Remove(id);
                        info.SlotHashes.Clear();
                        MySQL_Connection.Instance.RunQueryFormatted("DELETE FROM characters WHERE internal_id = {0}; UPDATE character_delete_queue SET parsed_at = NOW() WHERE id = {0};", id);
                        return true;
                    });

                    Logger.WriteLine("Removed {0} characters from database.", deletes);
                });

            }, null, 0, 30 * 1000);
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
#if LOCALE_EMS
                tmp.Add((ushort)ServerOpcodes.LOGIN_NORMAL, new Handler(ServerPacketHandlers.Instance.HandleLogin, null));
#else
                tmp.Add((ushort)ServerOpcodes.LOGIN_AFTER_WORLDSELECT, new Handler(ServerPacketHandlers.Instance.HandleLoginFromWeb, null));
#endif
                tmp.Add((ushort)ServerOpcodes.CHARACTER_CHOOSE, new Handler((pConnection, pPacket) =>
                {
                    short status = pPacket.ReadShort();
                    if (status == 0)
                    {
                        string ip = string.Format("{0}.{1}.{2}.{3} port {4}", pPacket.ReadByte(), pPacket.ReadByte(), pPacket.ReadByte(), pPacket.ReadByte(), pPacket.ReadUShort());
                        int charid = pPacket.ReadInt();
                        pConnection.Logger_WriteLine("Selected charid {0} and connects to {1}", charid, ip);
                    }
                }, null));
                tmp.Add((ushort)ServerOpcodes.CHARACTER_DELETE, new Handler(ServerPacketHandlers.Instance.HandleCharacterDeletion, OnCharacterSelect));
                tmp.Add((ushort)ServerOpcodes.CONNECT_TO_SERVER, new Handler((pConnection, pPacket) =>
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

                tmp.Add((ushort)ServerOpcodes.PING, new Handler((a, b) =>
                {
                    // ping
                }, null));

                tmp.Add((ushort)ServerOpcodes.INVENTORY_UPDATE, new Handler(ServerPacketHandlers.Instance.HandleInventoryUpdate, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.INVENTORY_SLOTS_UPDATE, new Handler(ServerPacketHandlers.Instance.HandleInventorySlotsUpdate, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.STAT_UPDATE, new Handler(ServerPacketHandlers.Instance.HandleStatUpdate, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.SKILL_UPDATE, new Handler(ServerPacketHandlers.Instance.HandleSkillUpdate, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.MESSAGE, new Handler(ServerPacketHandlers.Instance.HandleMessage, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.BUDDYLIST, new Handler(ServerPacketHandlers.Instance.HandleBuddyList, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.GUILD, new Handler(ServerPacketHandlers.Instance.HandleGuild, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.ALLIANCE, new Handler(ServerPacketHandlers.Instance.HandleAlliance, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.FAMILIARS, new Handler(ServerPacketHandlers.Instance.HandleFamiliarList, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.ABILITY_UPDATE, new Handler(ServerPacketHandlers.Instance.HandleAbilityInfoUpdate, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.MAPLEPOINTS, new Handler(ServerPacketHandlers.Instance.HandleMaplePointAmount, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.SKILL_MACROS, new Handler(ServerPacketHandlers.Instance.HandleSkillMacros, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.CHANGE_MAP, new Handler(ServerPacketHandlers.Instance.HandleChangeMap, IdentifiedAccountAndUser));
                tmp.Add((ushort)ServerOpcodes.SPAWN_PLAYER, new Handler(ServerPacketHandlers.Instance.HandleSpawnPlayer, OnLoadedCharData));
                tmp.Add((ushort)ServerOpcodes.SPAWN_ANDROID, new Handler(ServerPacketHandlers.Instance.HandleSpawnAndroid, OnLoadedCharData));
                //tmp.Add(0x02B1, new Handler(ServerPacketHandlers.Instance.HandleTradeData, NeedsCharData));

                tmp.Add((ushort)ServerOpcodes.KEYMAP_UPDATE, new Handler(ServerPacketHandlers.Instance.HandleKeymap, OnLoadedCharData));

                ValidHeaders[(byte)MaplePacket.CommunicationType.ServerPacket] = tmp;
            }

            {
                // V.140 -> 141: 0x0014 -> 0x003F, Pong: 0x002D -> 0x0046
                // Client Packets
                var tmp = new Dictionary<ushort, Handler>();
                tmp.Add((ushort)ClientOpcodes.VERSION_INFO, new Handler(ClientPacketHandlers.Instance.HandleVersion, null)); // Client Version

                tmp.Add((ushort)ClientOpcodes.SELECT_CHANNEL, new Handler((pConnection, pPacket) =>
                {
#if LOCALE_GMS
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
#elif LOCALE_EMS
                    byte requestType = pPacket.ReadByte();
                    if (requestType != 0)
                    {
                        pConnection.Logger_WriteLine("Error selecting channel! {0}", requestType);

                        return;
                    }
#endif

                    pConnection.WorldID = pPacket.ReadByte();
                    pConnection.ChannelID = pPacket.ReadByte(); // Channel ID
                    pPacket.ReadInt(); // Client LAN IP -.-: 192.168.0.212

                    pConnection.Logger_WriteLine("User selected World {0} Channel {1}", pConnection.WorldID, pConnection.ChannelID);
                }, null));

                tmp.Add((ushort)ClientOpcodes.CHARACTER_LOAD, new Handler(ClientPacketHandlers.Instance.HandleCharacterLoadRequest, null));

                // Pong
                tmp.Add((ushort)ClientOpcodes.PONG, new Handler((pConnection, pPacket) =>
                {
                }, null));

                tmp.Add((ushort)ClientOpcodes.CHANGE_CHANNEL_REQUEST, new Handler((pConnection, pPacket) =>
                {
                    byte new_channel = pPacket.ReadByte();
                    pConnection.Logger_WriteLine("Requesting CC to channel {0}", new_channel);
                }, null));

                // Whisper
                tmp.Add((ushort)ClientOpcodes.WHISPER, new Handler((pConnection, pPacket) =>
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


                tmp.Add((ushort)ClientOpcodes.KEYMAP_UPDATE, new Handler(ClientPacketHandlers.Instance.HandleKeymapUpdate, OnLoadedCharData));



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