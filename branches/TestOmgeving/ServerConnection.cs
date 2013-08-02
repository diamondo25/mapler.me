using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Mapler_Client
{
    class ServerConnection : MESession
    {
        public static ServerConnection Instance { get; private set; }
        public static void Initialize()
        {
            string domain = "mc.craftnet.nl";
            if (System.IO.File.Exists("server.txt")) domain = System.IO.File.ReadAllText("server.txt");
            Instance = new ServerConnection(domain);
        }

        private List<ushort>[] _validHeaders;
        public List<string> AcceptedIPs { get; private set; }
        public byte[] MapleStoryCryptoKey { get; private set; }
        public ushort AcceptedMapleStoryVersion { get; private set; }
        public byte AcceptedMapleStoryLocale { get; private set; }
        
        public ServerConnection(string pDomain)
            : base(pDomain, 23710)
        {
            AcceptedIPs = new List<string>();
        }

        public override void OnDisconnect()
        {
            if (!Program.Closing)
            {
                Logger.WriteLine("Trying to reconnect...");
                Sniffer.Instance.Stop();
                TryReconnect();
            }
        }

        private void TryReconnect()
        {
            new System.Threading.Thread((a) =>
            {
                for (int i = 0; Disconnected && i < 5; i++)
                {
                    Logger.WriteLine("Connection try {0}", i);
                    try
                    {
                        System.Threading.Thread.Sleep(5000);
                        Connect();
                        
                    }
                    catch
                    {
                        Logger.WriteLine("Still not connected...");
                    }
                }

                if (Disconnected)
                {
                    if (frmMain.Instance != null)
                    {
                        frmMain.Instance.Invoke((System.Windows.Forms.MethodInvoker)delegate
                        {
                            System.Windows.Forms.MessageBox.Show(frmMain.Instance, "You were disconnected from Mapler.me.\r\nThis application will now exit.", "Mapler.me server connection error", System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Error);
                        });
                    }
                    else
                    {
                        System.Windows.Forms.MessageBox.Show("You were disconnected from Mapler.me.\r\nThis application will now exit.", "Mapler.me server connection error", System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Error);
                    }

                    Environment.Exit(1);
                }
                else
                {
                    Logger.WriteLine("Reconnected. Starting sniffer again");
                    
                    Sniffer.Instance.SetUp();
                }

            }).Start();
        }

        public override void OnPacket(MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            ushort header = pPacket.ReadUShort();
            if (header >= 0xEE00)
            {
                if (header == 0xEEFF)
                {
                    string version = pPacket.ReadString();
                    if (version != Logger.Version)
                    {
                        
                        if (frmMain.Instance != null)
                        {
                            frmMain.Instance.Invoke((System.Windows.Forms.MethodInvoker)delegate
                            {
                                System.Windows.Forms.MessageBox.Show(frmMain.Instance, "You are using an outdated version of Mapler.me! Check the site for the latest updates.", "Mapler.me server connection error", System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Error);
                            });
                        }
                        else
                        {
                            System.Windows.Forms.MessageBox.Show("You are using an outdated version of Mapler.me! Check the site for the latest updates.", "Mapler.me server connection error", System.Windows.Forms.MessageBoxButtons.OK, System.Windows.Forms.MessageBoxIcon.Error);
                        }
                        Environment.Exit(3);
                        return;
                    }


                    // Crypto
                    byte[] sendkey = pPacket.ReadBytes(32),
                        recvkey = pPacket.ReadBytes(32);

                    SetKeys(sendkey, recvkey);

                    _validHeaders = new List<ushort>[(byte)MaplePacket.CommunicationType.AMOUNT];
                    for (byte i = 0; i < (byte)MaplePacket.CommunicationType.AMOUNT; i++)
                    {
                        _validHeaders[i] = new List<ushort>();
                        for (ushort j = pPacket.ReadUShort(); j > 0; j--)
                        {
                            ushort tmp = pPacket.ReadUShort();
                            // Logger.WriteLine("{0} accepts 0x{1:X4}", (MaplePacket.CommunicationType)i, tmp);
                            _validHeaders[i].Add(tmp);
                        }
                    }


                    for (byte j = pPacket.ReadByte(); j > 0; j--)
                    {
                        string ip = pPacket.ReadString();
                        AcceptedIPs.Add(ip);
                    }

                    MapleStoryCryptoKey = pPacket.ReadBytes(32);

                    AcceptedMapleStoryLocale = pPacket.ReadByte();
                    AcceptedMapleStoryVersion = pPacket.ReadUShort();
                    Logger.WriteLine("Initialized keys and valid headers");
                }
                else if (header == 0xEEFE)
                {
                    // Create screenshot and send to server

                    string url = pPacket.ReadString();
                    string data = pPacket.ReadString();

                    string filename = System.IO.Path.GetTempFileName();

                    bool done = Screenshot.MakeScreenshotOfMaple(filename);
                    if (done)
                    {
                        Screenshot.Upload(url, data, filename);
                    }
                }
                else if (header == 0xEEFD)
                {
                    string charname = pPacket.ReadString();
                    frmMain.Instance.Invoke((System.Windows.Forms.MethodInvoker)delegate
                    {
                        frmMain.Instance.lblLastUpdate.Text = string.Format("{0} (Character: {1})", DateTime.Now, charname);
                    });
                }
                else if (header == 0xEEFC)
                {
                    frmMain.Instance.Invoke((System.Windows.Forms.MethodInvoker)delegate
                    {
                        frmMain.Instance.lblInfo.Text = pPacket.ReadString();
                    });
                }
                else if (header == 0xEE01)
                {
                    // Pingpong
                    using (MaplePacket mp = new MaplePacket(MaplePacket.CommunicationType.ClientPacket, 0xEE01))
                    {
                        SendPacket(mp);
                    }
                }
            }

            pPacket.Dispose();
            pPacket = null;
        }

        public void ForwardPacket(MaplePacket.CommunicationType pType, MaplePacket pPacket)
        {
            pPacket.Reset();

            ushort header = pPacket.ReadUShort();

            if (!_validHeaders[(byte)pType].Contains(header))
            {
                return;
            }

            using (MaplePacket packet = new MaplePacket(pType, header))
            {
                packet.WriteBytes(pPacket.ReadLeftoverBytes());
                SendPacket(packet);
            }
        }
    }
}
