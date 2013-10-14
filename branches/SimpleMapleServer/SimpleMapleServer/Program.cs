using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net.Sockets;
using System.Threading;
using System.Xml.Serialization;
using System.IO;

namespace SimpleMapleServer
{
    class Program
    {
        public const ushort CHANGE_MAP_HEADER = 0x010C;
        public static Dictionary<short, ItemEquip> ITEMS;

        static void Main(string[] args)
        {
            GMSKeys.Initialize();

            {
                // Quick test
                ITEMS = new Dictionary<short, ItemEquip>();
                ITEMS.Add(1, new ItemEquip(1113017)
                {
                    StatusFlags = 0x0714,
                    Potential1 = 40356,
                    Potential2 = 30041,
                    Potential3 = 30044,
                    Potential4 = 12011,
                    Potential5 = 2014,
                    Potential6 = 2014,
                    SocketState = 0x00FF,
                    Nebulite2 = 1001,
                    Nebulite1 = 2001,
                    Nebulite3 = 3400,

                });

                //File.WriteAllText("import.xml", ITEMS.Serialize());

                if (File.Exists("import.xml"))
                {
                    ITEMS = File.ReadAllText("import.xml").Deserialize<Dictionary<short, ItemEquip>>();
                }
            }

            if (ITEMS == null)
                ITEMS = new Dictionary<short, ItemEquip>();

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
            }



            Console.ReadLine();
        }
    }
}
