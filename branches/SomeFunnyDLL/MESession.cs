using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net;
using System.Net.Sockets;

namespace System
{
    public abstract class MESession : IDisposable
    {
        private Socket _socket;
        private byte[] _sendKey = null, _receiveKey = null;
        private byte[] _receiveBuffer;
        private int _receivePosition;
        private int _receiveLength;
        private bool _header = true;

        public string IP { get; private set; }
        public ushort Port { get; private set; }

        private bool _disconnected = false;
        public bool Disconnected { get { return _disconnected; } }

        public IPEndPoint HostEndPoint { get; private set; }
        public IPEndPoint ClientEndPoint { get; private set; }

        public MESession() { }

        public MESession(Socket pSocket)
        {
            _socket = pSocket;
            HostEndPoint = pSocket.LocalEndPoint as IPEndPoint;
            ClientEndPoint = pSocket.RemoteEndPoint as IPEndPoint;


            IP = ClientEndPoint.Address.ToString();
            Port = (ushort)ClientEndPoint.Port;
            StartReceive(4, false);
        }

        public MESession(string pHostname, ushort pPort)
        {
            _disconnected = true;
            this.IP = pHostname;
            this.Port = pPort;

            Connect();
        }

        protected void Connect()
        {
            _receiveKey = null;
            _sendKey = null;
            _header = true;
            IPAddress[] addies;
            try
            {
                addies = Dns.GetHostAddresses(this.IP);
            }
            catch (Exception ex)
            {
                throw new Exception(string.Format("Unable to retrieve DNS addresses for hostname '{0}'", this.IP), ex);
            }

            _socket = new Socket(AddressFamily.InterNetwork, SocketType.Stream, ProtocolType.Tcp);

            Exception lastException = null;
            string lastHost = "";
            foreach (IPAddress addr in addies)
            {
                try
                {
                    _socket.Connect(addr, this.Port);
                    lastException = null;
                    break;
                }
                catch (Exception ex)
                {
                    lastException = ex;
                    lastHost = addr.ToString();
                }
            }
            if (lastException != null)
            {
                throw new Exception(string.Format("Unable to connect to remote host @ {0}:{1}", lastHost, this.Port), lastException);
            }
            _disconnected = false;
            StartReceive(4, false);
        }

        ~MESession()
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
            Disconnect();
            _sendKey = null;
            _receiveKey = null;
            _receiveBuffer = null;
        }

        public void Disconnect()
        {
            if (!_disconnected && _socket != null)
            {
                _disconnected = true;
                if (_socket.Connected)
                {
                    _socket.Shutdown(SocketShutdown.Both);
                }
                _socket.Close();
                _socket = null;
                OnDisconnect();
            }
        }

        public virtual void OnPacket(MaplePacket pPacket) { }
        public virtual void OnDisconnect() { }

        public void StartReceive(int pLength, bool pIsContinue)
        {
            if (_disconnected) return;
            if (_receiveBuffer == null)
                _receiveBuffer = new byte[1024];
            if (!pIsContinue)
            {
                _receivePosition = 0;
                _receiveLength = pLength;
                if (_receiveBuffer.Length < _receiveLength)
                    Array.Resize<byte>(ref _receiveBuffer, _receiveLength); // Resize buffer
            }
            try
            {
                _socket.BeginReceive(_receiveBuffer, _receivePosition, _receiveLength - _receivePosition, SocketFlags.None, EndReceive, null);
            }
            catch (Exception)
            {
                Logger.WriteLine("Internal Disconnection. StartReceive");
                OnDisconnect();
            }
        }

        private void EndReceive(IAsyncResult pIAR)
        {
            if (_disconnected) return;
            try
            {
                int dataLength = 0;
                try
                {
                    dataLength = _socket.EndReceive(pIAR);

                    if (dataLength == 0)
                        throw new Exception();
                }
                catch (Exception)
                {
                    Logger.WriteLine("Internal Disconnection. EndReceive");
                    Disconnect();
                    return;
                }

                _receivePosition += dataLength;

                if (_receivePosition == _receiveLength)
                {
                    // Got packet.
                    if (_header)
                    {
                        // Get length
                        int newlen = BitConverter.ToInt32(_receiveBuffer, 0);
                        newlen += 4;
                        _header = false;
                        StartReceive(newlen, false);
                    }
                    else
                    {
                        int checksum = BitConverter.ToInt32(_receiveBuffer, 0);


                        byte[] realdata = new byte[_receiveLength - 4];
                        Buffer.BlockCopy(_receiveBuffer, 4, realdata, 0, realdata.Length);
                        int curchecksum = realdata.CalculateChecksum();

                        if (checksum != curchecksum)
                        {
                            Logger.WriteLine("Invalid Checksum! {0} != {1}", checksum, curchecksum);
                        }
                        else
                        {
                            if (_receiveKey != null)
                                realdata = Crypto.Decrypt(realdata, _receiveKey);

                            MaplePacket packet = new MaplePacket(realdata);
                            try
                            {
                                OnPacket(packet);
                            }
                            catch (Exception ex)
                            {
                                Logger.WriteLine("Internal Packet Handling Exception");
                                throw new Exception("Internal Packet Handling Exception", ex);
                            }
                        }

                        _header = true;
                        StartReceive(4, false);
                    }
                }
                else
                {
                    StartReceive(_receiveLength - _receivePosition, true);
                }
            }
            catch (Exception ex)
            {
                Logger.ErrorLog("Error @ Packet Handling:\r\n {0}", ex.ToString());
                Disconnect();
            }
        }

        public void SetKeys(byte[] pSendKey, byte[] pReceiveKey)
        {
            _sendKey = pSendKey;
            _receiveKey = pReceiveKey;
        }

        public virtual void SendPacket(MaplePacket pPacket)
        {
            if (_disconnected) return;
            try
            {

                byte[] data = pPacket.ToArray();
                if (_sendKey != null)
                    data = Crypto.Encrypt(data, _sendKey);

                byte[] completeData = new byte[data.Length + 4 + 4];
                Buffer.BlockCopy(BitConverter.GetBytes(pPacket.Length), 0, completeData, 0, 4);
                Buffer.BlockCopy(BitConverter.GetBytes(data.CalculateChecksum()), 0, completeData, 4, 4);
                Buffer.BlockCopy(data, 0, completeData, 8, data.Length);

                _socket.Send(completeData, 0, completeData.Length, SocketFlags.None);
            }
            catch (Exception)
            {
                Logger.WriteLine("Internal Packet Sending Exception @ SendPacket");
                Disconnect();
            }
        }
    }
}