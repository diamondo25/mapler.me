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

        public string LastLoggedName = "Unknown";
        public string LastLoggedDate = null;

        public CharacterData CharData { get; set; }

        public ushort MapleVersion { get; set; }

        private MSBExporter _exporter;

        public ClientConnection(MSBLoader pLoader)
        {
            Program.Clients.Add(this);
            Logger_WriteLine("Fake Client Connected!");
            Clear();
            pLoader.PacketHandler += OnPacket;
        }

        public ClientConnection(Socket pSocket)
            : base(pSocket)
        {
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
            using (MaplePacket tmp = new MaplePacket(pPacket.ToArray()))
            {
                _exporter.AddPacket(tmp);
            }
            base.SendPacket(pPacket);
        }

        public void Save(bool pReset)
        {
            Logger_WriteLine("Trying to save...");
            if (_exporter != null)
            {
                string filename = "Savefile_" + LastLoggedName + "-" + (LastLoggedDate == null ? MasterThread.CurrentDate.ToString("ddMMyyyy-HHmss") : LastLoggedDate) + ".msb";

                Logger_WriteLine("Saving under {0}", filename);
                _exporter.Save(filename, MapleVersion, base.HostEndPoint, base.ClientEndPoint);
                if (pReset)
                {
                    _exporter = new MSBExporter();
                }
                else
                {
                    _exporter = null;
                }
            }
        }

        public override void OnDisconnect()
        {
            Save(false);
            Logger_WriteLine("Client Disconnected.");
            Clear();
            Program.Clients.Remove(this);
        }

        public void SendTimeUpdate()
        {
            using (MaplePacket packet = new MaplePacket(MaplePacket.CommunicationType.ServerPacket, 0xEEFD))
            {
                packet.WriteString(LastLoggedName);
                SendPacket(packet);
            }
        }

        public void SendInfoText(string pMessage, params object[] pParams)
        {
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
                    ushort header = pPacket.ReadUShort();

                    if ((byte)type < Program.ValidHeaders.Length)
                    {
                        // Check if packet is accepted
                        var list = Program.ValidHeaders[(byte)type];
                        if (list.ContainsKey(header))
                        {
                            var action = list[header];
                            if (action != null)
                            {
                                try
                                {
                                    if (action.CanHandle == null || action.CanHandle(this))
                                    {
                                        action.Handle(this, pPacket);
                                    }
                                }
                                catch (Exception ex)
                                {
                                    Logger_ErrorLog("Failed parsing {0:X4} for {1}:\r\n{2}", header, type, ex.ToString());
                                }
                            }
                            else
                            {
                                Logger_WriteLine("No action for {0:X4}", header);
                            }
                        }
                        else
                        {
                            Logger_WriteLine("Client sent packet {0:X4} for {1} but this one is not handled!", header, type);
                        }
                    }
                    else
                    {
                        Logger_ErrorLog("Packet Type not accepted!!! {0:X4} {1}", header, (byte)type);
                    }
                }
                catch (Exception ex)
                {
                    Logger.ErrorLog("Failed handling packet: {0}", ex.ToString());
                }
                pPacket.Dispose();
                pPacket = null;
            });
        }


        public void Logger_WriteLine(string pFormat, params object[] pParams)
        {
            string msg = string.Format(pFormat, pParams);

            Logger.WriteLine("[{0}] {1}", LastLoggedName, msg);
        }


        public void Logger_ErrorLog(string pFormat, params object[] pParams)
        {
            string msg = string.Format(pFormat, pParams);

            Logger.ErrorLog("[{0}] {1}", LastLoggedName, msg);
        }
    }
}
