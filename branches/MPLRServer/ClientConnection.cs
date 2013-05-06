using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net.Sockets;

namespace MPLRServer
{
    class ClientConnection : MESession
    {
        public int AccountID { get; set; }
        public int UserID { get; set; }
        public int CharacterID { get; set; }
        public int CharacterInternalID { get; set; }
        public byte WorldID { get; set; }
        public byte ChannelID { get; set; }

        public int LastReportID { get; set; }

        public string LogFilename = "Unknown";
        public string LastLoggedCharacterName = "Unknown";
        public string LastLoggedDate = null;

        public CharacterData CharData { get; set; }

        public ushort MapleVersion { get; set; }

        private MSBExporter _exporter;
        public bool IsFake { get; private set; }

        public bool Pong { get; set; }

        public DateTime ConnectedTimeToServer = DateTime.MinValue;

        public ClientConnection(MSBLoader pLoader)
        {
            Pong = true;
            Program.Clients.Add(this);
            Logger_WriteLine("Fake Client Connected!");
            IsFake = true;
            Clear();
            pLoader.PacketHandler += OnPacket;
            pLoader.DisconnectHandler += OnDisconnect;
        }

        public ClientConnection(Socket pSocket)
            : base(pSocket)
        {
            Pong = true;
            IsFake = false;
            Program.Clients.Add(this);
            Clear();
            _exporter = new MSBExporter();
            Logger_WriteLine("Client Connected!");

            byte[] sendkey = new byte[32], recvkey = new byte[32];
            Program.Random.NextBytes(sendkey);
            Program.Random.NextBytes(recvkey);

            using (MaplePacket pack = new MaplePacket(MaplePacket.CommunicationType.ServerPacket, 0xEEFF))
            {
                pack.WriteString(Logger.Version);

                // Add encryption keys
                pack.WriteBytes(recvkey);
                pack.WriteBytes(sendkey);

                for (byte i = 0; i < (byte)MaplePacket.CommunicationType.AMOUNT; i++)
                {
                    pack.WriteUShort((ushort)Program.ValidHeaders[i].Keys.Count);
                    foreach (var header in Program.ValidHeaders[i].Keys)
                        pack.WriteUShort(header);
                }

                pack.WriteByte((byte)Program.AcceptedIPs.Count);
                foreach (string ip in Program.AcceptedIPs)
                    pack.WriteString(ip);

                pack.WriteBytes(GMSKeys.GetKeyForVersion());

                SendPacket(pack);
            }

            SetKeys(sendkey, recvkey);


            SendInfoText("Welcome! Please open MapleStory.");
        }

        private void Clear()
        {
            AccountID = -1;
            UserID = -1;
            CharacterInternalID = CharacterID = -1;
            _exporter = null;
            CharData = null;
            LastReportID = -1;
        }

        public override void SendPacket(MaplePacket pPacket)
        {
            if (IsFake) return;
            using (MaplePacket tmp = new MaplePacket(pPacket.ToArray()))
            {
                _exporter.AddPacket(tmp);
            }
            base.SendPacket(pPacket);
        }

        public void Save(bool pReset, bool pClean = true)
        {
            if (IsFake) return;

            Logger_WriteLine("Trying to save...");
            if (_exporter != null)
            {
                // Try to get logfile ID from DB
                string filename = "Savefile_";
                int packets = _exporter.GetSize();
                try
                {
                    using (InsertQueryBuilder iqb = new InsertQueryBuilder("session_logfiles"))
                    {
                        iqb.AddColumns(false, "id", "information", "at");
                        iqb.AddRow(null, packets + " > " + LogFilename, new MySQL_Connection.NowType());
                        iqb.RunQuery();

                        filename += MySQL_Connection.Instance.GetLastInsertId();
                    }
                }
                catch
                {
                    filename += "NOMYSQL_" + MasterThread.CurrentDate.ToFileTime();
                }
                filename += ".msb";

                Logger_WriteLine("Saving packets to '{0}' ({1} packets logged)", filename, _exporter.GetSize());
                _exporter.Save(filename, ClientPacketHandlers.LatestMajorVersion, base.HostEndPoint, base.ClientEndPoint);
                if (pReset)
                    _exporter = new MSBExporter();
                else if (pClean)
                    _exporter = null;
            }
        }

        public override void OnDisconnect()
        {
            if (MasterThread.Instance.IsInMainThread())
            {
                Save(false);
                Logger_WriteLine("Client Disconnected.");
                Clear();
                Program.Clients.Remove(this);
            }
            else
            {
                MasterThread.Instance.AddCallback((a) =>
                {
                    Save(false);
                    Logger_WriteLine("Client Disconnected.");
                    Clear();
                    Program.Clients.Remove(this);
                });
            }
        }

        public void SendTimeUpdate()
        {
            if (IsFake) return;
            using (MaplePacket packet = new MaplePacket(MaplePacket.CommunicationType.ServerPacket, 0xEEFD))
            {
                packet.WriteString(LastLoggedCharacterName);
                SendPacket(packet);
            }
        }

        public void SendInfoText(string pMessage, params object[] pParams)
        {
            if (IsFake) return;
            using (MaplePacket packet = new MaplePacket(MaplePacket.CommunicationType.ServerPacket, 0xEEFC))
            {
                packet.WriteString(string.Format(pMessage, pParams));
                SendPacket(packet);
            }
        }

        public override void OnPacket(MaplePacket pPacket)
        {
            MasterThread.Instance.AddCallback((a) =>
            {
                try
                {
                    if (_exporter != null)
                        _exporter.AddPacket(pPacket);

                    MaplePacket.CommunicationType type = (MaplePacket.CommunicationType)pPacket.ReadByte();
                    ushort opcode = pPacket.ReadUShort();

                    if (IsFake)
                    {
                        Logger.WriteLine("Emulating {0:X4} (Len: {1})", opcode, pPacket.Length);
                    }

                    if ((byte)type < Program.ValidHeaders.Length)
                    {
                        // Check if packet is accepted
                        var list = Program.ValidHeaders[(byte)type];
                        if (list.ContainsKey(opcode))
                        {
                            var action = list[opcode];
                            if (action != null)
                            {
                                try
                                {
                                    if (action.CanHandle == null || action.CanHandle(this))
                                        action.Handle(this, pPacket);
                                }
                                catch (Exception ex)
                                {
                                    Logger_ErrorLog("Failed parsing {0:X4} for {1}:\r\n{2}", opcode, type, ex.ToString());
                                    LogFilename += "ERROR";
                                    SendInfoText("An error occurred on the Mapler.me server! Please report this :)");

                                    // Save exception to packet
                                    using (MaplePacket mp = new MaplePacket(0x9999))
                                    {
                                        mp.WriteString(ex.ToString());
                                        if (ex is MySql.Data.MySqlClient.MySqlException)
                                        {
                                            Logger_ErrorLog("MySQL exception!");
                                            var queries = MySQL_Connection.Instance.GetRanQueries();
                                            mp.WriteInt(queries.Count);
                                            foreach (var kvp in queries)
                                            {
                                                mp.WriteString(kvp.Key);
                                                mp.WriteString(kvp.Value);
                                            }

                                            mp.SwitchOver(); // Make read packet
                                        }
                                        _exporter.AddPacket(mp);
                                    }

                                    Save(false, false);
                                }
                            }
                            else
                            {
                                Logger_WriteLine("No action for {0:X4}", opcode);
                            }
                        }
                        else
                        {
                            Logger_WriteLine("Client sent packet {0:X4} for {1} but this one is not handled!", opcode, type);
                        }
                    }
                    else
                    {
                        Logger_ErrorLog("Packet Type not accepted!!! {0:X4} {1}", opcode, (byte)type);
                    }
                }
                catch (Exception ex)
                {
                    Logger.ErrorLog("Failed handling packet: {0}", ex.ToString());
                    SendInfoText("An error occurred on the Mapler.me server! Please report this :)");
                }
                pPacket.Dispose();
                pPacket = null;
            });
        }


        public void Logger_WriteLine(string pFormat, params object[] pParams)
        {
            string msg = string.Format(pFormat, pParams);

            Logger.WriteLine("[{0}] {1}", LastLoggedCharacterName, msg);
        }


        public void Logger_ErrorLog(string pFormat, params object[] pParams)
        {
            string msg = string.Format(pFormat, pParams);

            Logger.ErrorLog("[{0}] {1}", LastLoggedCharacterName, msg);
        }
    }
}
