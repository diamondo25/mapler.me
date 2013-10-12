using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net.Sockets;
using System.Threading;

namespace SimpleMapleServer
{
    class Program
    {
        public const ushort CHANGE_MAP_HEADER = 0x010C;

        static void Main(string[] args)
        {
            TcpListener listener = new TcpListener(System.Net.IPAddress.Any, 8484);
            listener.Start();

            AsyncCallback EndAccept = null;
            EndAccept = (a) =>
            {
                new Client(listener.EndAcceptSocket(a));

                Console.WriteLine("accepted");

                listener.BeginAcceptSocket(EndAccept, null);
            };

            listener.BeginAcceptSocket(EndAccept, null);

            GMSKeys.Initialize();

            Console.ReadLine();
        }
    }
}
