﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net.Sockets;
using System.IO;

namespace MPLRServer
{
    public class ClientConnection : MESession
    {
        public int AccountID { get; set; }
        public int UserID { get; set; }
        public int CharacterID { get; set; }
        public int CharacterInternalID { get; set; }
        public byte WorldID { get; set; }
        public byte ChannelID { get; set; }
        public byte[] MachineID { get; set; }
        public byte LastExpPoint { get; set; }

        public string ConnectedToIP { get; set; }
        public ushort ConnectedToPort { get; set; }

        public int LastReportID { get; set; }

        public string LogFilename = "Unknown";
        public string LastLoggedCharacterName = "Unknown";
        public string LastLoggedDate = null;

        public CharacterData CharData { get; set; }
        public List<string> _CharactersInMap { get; set; }

        public ushort MapleVersion { get; set; }

        private MSBExporter _exporter;
        public bool IsFake { get; private set; }

        public bool Pong { get; set; }

        public DateTime ConnectedTimeToServer = DateTime.MinValue;

        private int uniqueid = 0;

        public override string ToString()
        {

            return this.LastLoggedCharacterName + " (@ " + ConnectedToIP + ":" + ConnectedToPort + " | " + WorldID + ", " + ChannelID + ")";
        }

        public ClientConnection(MSBLoader pLoader)
        {
            uniqueid = Program.Random.Next(0, 10000);
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
            uniqueid = Program.Random.Next(0, 10000);

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


#if LOCALE_GMS
                pack.WriteBool(true);
                pack.WriteBytes(GMSKeys.GetKeyForVersion());
#elif LOCALE_KMS
                pack.WriteBool(false);
#else
                pack.WriteBool(true);
                pack.WriteBytes(new byte[] { 0x13, 0x00, 0x00, 0x00, 0x08, 0x00, 0x00, 0x00, 0x06, 0x00, 0x00, 0x00, 0xB4, 0x00, 0x00, 0x00,
            0x1B, 0x00, 0x00, 0x00, 0x0F, 0x00, 0x00, 0x00, 0x33, 0x00, 0x00, 0x00, 0x52, 0x00, 0x00, 0x00 });
#endif


                pack.WriteByte(ServerMapleInfo.LOCALE);
                pack.WriteUShort(ServerMapleInfo.VERSION);

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
            _CharactersInMap = new List<string>();
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

                if (!Directory.Exists("sessions"))
                    Directory.CreateDirectory("sessions");

                string filename = "sessions/Savefile_";
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
                _exporter.Save(filename, ServerMapleInfo.VERSION, base.HostEndPoint, base.ClientEndPoint);
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
                //Save(false);
                if (_exporter != null) _exporter.Clear(true);
                _exporter = null;
                var info = SessionRestartCache.Instance.GetInfoForConnection(this);
                if (info != null)
                    SessionRestartCache.Instance.RemoveInfo(info);

                Logger_WriteLine("Client Disconnected.");
                Clear();
                Program.Clients.Remove(this);
            }
            else
            {
                MasterThread.Instance.AddCallback((a) =>
                {
                    if (_exporter != null) _exporter.Clear(true);
                    _exporter = null;
                    //Save(false);

                    var info = SessionRestartCache.Instance.GetInfoForConnection(this);
                    if (info != null)
                        SessionRestartCache.Instance.RemoveInfo(info);

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
                    if (Disconnected) return; // Just to be sure...

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
                                    Logger_ErrorLog("Failed parsing {0:X4} for {1}", opcode, type);
                                    Logger_WriteLine(ex.ToString());
                                    if (!IsFake)
                                    {
                                        LogFilename += "ERROR";
                                        SendInfoText("An error occurred on the Mapler.me server! Please report this :)");

                                        // Save exception to packet
                                        using (MaplePacket mp = new MaplePacket(MaplePacket.CommunicationType.ServerPacket, 0x9999))
                                        {
                                            mp.WriteString(ex.ToString());
                                            if (ex.ToString().Contains("MySql.Data.MySqlClient.MySqlException"))
                                            {
                                                Logger_ErrorLog("MySQL exception!");
                                                var queries = MySQL_Connection.Instance.GetRanQueries();
                                                mp.WriteInt(queries.Count);
                                                foreach (var kvp in queries)
                                                {
                                                    mp.WriteString(kvp.Key);
                                                    mp.WriteString(kvp.Value);
                                                }

                                            }
                                            mp.SwitchOver(); // Make read packet
                                            _exporter.AddPacket(mp);
                                        }

                                        Save(false, false);
                                    }
                                }
                            }
                            else
                            {
                                if (!IsFake)
                                    Logger_WriteLine("No action for {0:X4}", opcode);
                            }
                        }
                        else
                        {
                            if (!IsFake)
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

            foreach (var line in msg.Split(new string[] { "\r\n" }, StringSplitOptions.RemoveEmptyEntries))
                Logger.WriteLine("[{2:X8}|{0}] {1}", LastLoggedCharacterName, line, uniqueid);
        }


        public void Logger_ErrorLog(string pFormat, params object[] pParams)
        {
            string msg = string.Format(pFormat, pParams);
            foreach (var line in msg.Split(new string[] { "\r\n" }, StringSplitOptions.RemoveEmptyEntries))
                Logger.ErrorLog("!!! [{2:X8}|{0}] {1}", LastLoggedCharacterName, line, uniqueid);
        }


        public int GetCharacterExpForTimeline()
        {
            if (CharData == null) return 0;

            float fullPercent = EXPTable.GetLevelPercentage(this.CharData.Stats.Level, this.CharData.Stats.EXP);
            return (int)Math.Round(fullPercent * 100);
        }
    }
}
