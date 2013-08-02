using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using SharpPcap;
using SharpPcap.AirPcap;
using SharpPcap.WinPcap;
using SharpPcap.LibPcap;
using PacketDotNet;

namespace Mapler_Client
{
    class Sniffer : IDisposable
    {
        public static Sniffer Instance { get; private set; }
        public static void Init()
        {
            Instance = new Sniffer();
            Instance.SetUp();
        }

        private List<ICaptureDevice> _devices = new List<ICaptureDevice>();
        public bool FoundConnection { get; private set; }
        private KeyValuePair<ushort, ushort> _currentPortMap;
        private Session _currentSession;

        public Sniffer()
        {
            FoundConnection = false;
        }

        ~Sniffer()
        {
            Dispose(false);
        }

        public void Dispose()
        {
            Dispose(true);
            GC.SuppressFinalize(this);
        }

        protected virtual void Dispose(bool disposing)
        {
            Stop();
            _devices = null;
        }

        public void Stop()
        {
            var tmp = new List<ICaptureDevice>(_devices);
            foreach (var device in tmp)
                StopCapture(device);
            tmp.Clear();
            _devices.Clear();

            FoundConnection = false;

            if (_currentSession != null)
            {
                _currentSession.Dispose();
                _currentSession = null;
            }


        }


        private void StopCapture(ICaptureDevice pDevice)
        {
            if (_devices.Contains(pDevice))
                _devices.Remove(pDevice);
            pDevice.Close();
            pDevice.OnPacketArrival -= device_OnPacketArrival;
        }

        public void SetUp()
        {
            var devices = CaptureDeviceList.Instance;
            // Sniff all devices
            foreach (var device in devices)
            {
                device.Open(DeviceMode.Promiscuous);
                device.Filter = "tcp portrange 8383-9000";
                Logger.WriteLine("[DEBUG] Set filter for {0}", device.Description);
                device.OnPacketArrival += device_OnPacketArrival;
                device.StartCapture();
                _devices.Add(device);

                System.Net.NetworkInformation.PhysicalAddress mac = null;
                try
                {
                    mac = device.MacAddress;
                }
                catch
                {
                    Logger.WriteLine("Unable to get mac address!!!");
                }



                if (device.LinkType != LinkLayers.Ethernet)
                {
                    Logger.WriteLine("Skipped sniffing {0} ({1})", device.Description, mac);
                    StopCapture(device);
                }
                else
                {
                    Logger.WriteLine("Started sniffing {0} ({1})", device.Description, mac);
                }
            }
        }

        private bool CheckIfCorrectIP(string pIP)
        {
            return ServerConnection.Instance.AcceptedIPs.Find((found) => { return pIP.Contains(found); }) != null;
        }

        int cache = 0;

        void device_OnPacketArrival(object sender, CaptureEventArgs e)
        {
            var packet = Packet.ParsePacket(e.Packet.LinkLayerType, e.Packet.Data);

            // Check if IP packet
            IpPacket ipPacket = packet.Extract(typeof(IpPacket)) as IpPacket;
            if (ipPacket == null)
                return;

            if (!CheckIfCorrectIP(ipPacket.SourceAddress.ToString()) && !CheckIfCorrectIP(ipPacket.DestinationAddress.ToString()))
            {
                return;
            }


            TcpPacket tcpPacket = packet.Extract(typeof(TcpPacket)) as TcpPacket;
            if (tcpPacket == null)
                return;

            if (!FoundConnection)
            {
                if (tcpPacket.Syn && tcpPacket.Ack)
                {

                    // Found new connection.

                    // Check version...

                    if (frmMain.Instance.CheckRunningEXEVersion())
                    {
                        // Correct version
                        FoundConnection = true;
                        _currentPortMap = new KeyValuePair<ushort, ushort>(tcpPacket.DestinationPort, tcpPacket.SourcePort);
                        _currentSession = new Session();

                        MasterThread.Instance.AddCallback((a) =>
                        {
                            using (MaplePacket p = new MaplePacket(0xEE00))
                            {
                                p.WriteBool(true);
                                p.WriteString(ipPacket.SourceAddress.ToString());
                                p.WriteUShort(tcpPacket.SourcePort);
                                p.SwitchOver();
                                p.Reset(0);
                                ServerConnection.Instance.ForwardPacket(MaplePacket.CommunicationType.ClientPacket, p);
                            }
                        });

                        Logger.WriteLine("[CON] New connection found on {0}!", e.Device.Description);
                        if (cache != 0)
                        {
                            _currentSession.SetOutboundSequence(cache);
                            cache = 0;
                        }
                        _currentSession.BufferTCPPacket(tcpPacket, !(_currentPortMap.Key == tcpPacket.SourcePort && _currentPortMap.Value == tcpPacket.DestinationPort));
                    }
                    else
                    {
                        Logger.WriteLine("Incorrect MapleStory version. Ignoring connection.");
                    }
                }
                else if (tcpPacket.Syn && !tcpPacket.Ack) // Heh fix
                {
                    cache = (int)(tcpPacket.SequenceNumber + 1);
                }
            }
            else if (FoundConnection && 
                (
                    (_currentPortMap.Key == tcpPacket.SourcePort && _currentPortMap.Value == tcpPacket.DestinationPort)
                    ||
                    (_currentPortMap.Value == tcpPacket.SourcePort && _currentPortMap.Key == tcpPacket.DestinationPort)
                )
                )
            {
                if (tcpPacket.Fin || tcpPacket.Rst)
                {
                    FoundConnection = false;
                    Logger.WriteLine("[CON] Connection Lost");

                    MasterThread.Instance.AddCallback((a) =>
                    {
                        using (MaplePacket p = new MaplePacket(0xEE00))
                        {
                            p.WriteBool(false);
                            p.SwitchOver();
                            p.Reset(0);
                            ServerConnection.Instance.ForwardPacket(MaplePacket.CommunicationType.ClientPacket, p);
                        }
                    });
                    return;
                }

                bool result = _currentSession.BufferTCPPacket(tcpPacket, !(_currentPortMap.Key == tcpPacket.SourcePort && _currentPortMap.Value == tcpPacket.DestinationPort));
                if (!result)
                {
                    FoundConnection = false;
                    _currentSession.Dispose();
                    _currentSession = null;
                }
            }
            else
            {
                // Logger.WriteLine("[DEBUG] {0} - {1} {2}", FoundConnection, tcpPacket.SourcePort, tcpPacket.DestinationPort);
            }
        }
    }
}
