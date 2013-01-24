using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net;
using System.Net.Sockets;

namespace MPLRServer
{
    class Acceptor
    {
        TcpListener _listener;

        public Acceptor(ushort pPort)
        {
            _listener = new TcpListener(IPAddress.Any, pPort);
            _listener.Start();
            StartAccept();
        }

        public void StartAccept()
        {
            _listener.BeginAcceptSocket(new AsyncCallback(EndAccept), null);
        }

        void EndAccept(IAsyncResult pIAR)
        {
            Socket sock = _listener.EndAcceptSocket(pIAR);
            new ClientConnection(sock);
            StartAccept();
        }
    }
}
