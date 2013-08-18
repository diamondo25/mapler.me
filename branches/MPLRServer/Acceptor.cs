using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net;
using System.Net.Sockets;

namespace MPLRServer
{
   public class Acceptor
    {
        TcpListener _listener;
        Action<Socket> _action;

        public Acceptor(ushort pPort, Action<Socket> pAction = null)
        {
            _listener = new TcpListener(IPAddress.Any, pPort);
            _listener.Start();
            _action = pAction;
            StartAccept();
        }

        public void StartAccept()
        {
            _listener.BeginAcceptSocket(new AsyncCallback(EndAccept), null);
        }

        void EndAccept(IAsyncResult pIAR)
        {
            Socket sock = _listener.EndAcceptSocket(pIAR);
            if (_action != null)
                _action(sock);
            StartAccept();
        }
    }
}
