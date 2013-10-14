using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace SimpleMapleServer
{
    class Client : Session
    {
        MSBExporter exporter;
        public Client(System.Net.Sockets.Socket pSocket)
            : base(pSocket, "Client")
        {
            exporter = new MSBExporter();
            this.SendHandshake(141, "2", 8);
        }

        public override void SendPacket(Packet pPacket)
        {
            pPacket.SwitchOver();
            exporter.AddPacket(pPacket, true);
            pPacket.SwitchOver();

            base.SendPacket(pPacket);
        }

        public override void OnDisconnect()
        {
            // Save!
            exporter.Save(string.Format("{0}.msb", DateTime.Now.ToString("yyyyMMddHHmm")), this.MapleVersion, new System.Net.IPEndPoint(System.Net.IPAddress.Parse("127.0.0.1"), 8484), new System.Net.IPEndPoint(System.Net.IPAddress.Parse(this.IP), this.Port));
            
            base.OnDisconnect();

            Console.WriteLine("Client disconnected");
        }

        public override void OnPacketInbound(Packet pPacket)
        {
            exporter.AddPacket(pPacket, false);

            ushort header = pPacket.ReadUShort();
            Console.WriteLine("Packet: {0}", pPacket.ToString());
            if (header == 0x0038) //(header == 0x0043)
            {
                // Version? > lets get rollin
                ServerPackets.EnterMapPacket(this, true, 180000000);

                short slot = 1;

                foreach (var kvp in Program.ITEMS)
                    ServerPackets.SendTestItem(this, kvp.Value, kvp.Key);

                /*

                ServerPackets.SendTestItem(this, 1003142, slot++);
                ServerPackets.SendTestItem(this, 1102275, slot++);

                ItemEquip equip = new ItemEquip(1222009)
                {
                    BattleModeDamage = 20,
                    BossDamageRate = 30,
                    Durability = 500000,
                    IgnoreDamageRate = 50,
                };
                ServerPackets.SendTestItem(this, equip, slot++);

                equip = new ItemEquip(1003142)
                {
                    AllStatsPercentage = 11,
                    TotalDamagePercentage = 22,
                    IncreasesSkills = 33
                };

                ushort statusflag = 0;
                statusflag |= 0x0020;
                //statusflag |= 0x2000;

                equip.StatusFlags = statusflag;

                ServerPackets.SendTestItem(this, equip, slot++);


                equip = new ItemEquip(1003142)
                {
                    Durability = 100,
                };
                ServerPackets.SendTestItem(this, equip, slot++);



                ServerPackets.SendTestItem(this, new ItemEquip(1113015) { StatusFlags = 0x0001 }, slot++); // Potential?
                ServerPackets.SendTestItem(this, new ItemEquip(1113015) { StatusFlags = 0x0002 }, slot++); // Potential?
                ServerPackets.SendTestItem(this, new ItemEquip(1113015) { StatusFlags = 0x0004 }, slot++); // Potential?
                ServerPackets.SendTestItem(this, new ItemEquip(1113015) { StatusFlags = 0x0008 }, slot++); // Potential?
                ServerPackets.SendTestItem(this, new ItemEquip(1113015) { StatusFlags = 0x0010 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113015) { StatusFlags = 0x0020 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113015) { StatusFlags = 0x0040 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113015) { StatusFlags = 0x0080 }, slot++);

                ServerPackets.SendTestItem(this, new ItemEquip(1113016) { SocketState = 0x0001 }, slot++); 
                ServerPackets.SendTestItem(this, new ItemEquip(1113016) { SocketState = 0x0002 }, slot++); 
                ServerPackets.SendTestItem(this, new ItemEquip(1113016) { SocketState = 0x0004 }, slot++); 
                ServerPackets.SendTestItem(this, new ItemEquip(1113016) { SocketState = 0x0008 }, slot++); 
                ServerPackets.SendTestItem(this, new ItemEquip(1113016) { SocketState = 0x001F }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113016) { SocketState = 0x002F }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113016) { SocketState = 0x004F }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113016) { SocketState = 0x008F }, slot++);

                ServerPackets.SendTestItem(this, new ItemEquip(1113017) { SocketState = 0x0100 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113017) { SocketState = 0x0200 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113017) { SocketState = 0x0400 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113017) { SocketState = 0x0800 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113017) { SocketState = 0x1000 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113017) { SocketState = 0x2000 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113017) { SocketState = 0x4000 }, slot++);
                ServerPackets.SendTestItem(this, new ItemEquip(1113017) { SocketState = 0x8000 }, slot++);

                ServerPackets.SendTestItem(this, new ItemEquip(1113017)
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

                }, slot++);
                */

            }
            else if (header == 0x0030)
            {
                int request = pPacket.ReadInt();
                int response;

                response = ((request >> 5) << 5) + (((((request & 0x1F) >> 3) ^ 2) << 3) + (7 - (request & 7)));
                response |= ((request >> 7) << 7);
                response -= 2;

                Packet packet = new Packet(0x0015);
                packet.WriteInt(response);
                this.SendPacket(packet);
            }
            else if (header == 0x040)
            {
                Packet packet = new Packet(0x0000);
                packet.WriteInt(0);
                packet.WriteShort(0);
                
                packet.WriteInt(12222222); // User ID
                packet.WriteByte(0);
                packet.WriteByte(0);
                packet.WriteUShort(0xFFFF); // admin flags
                packet.WriteInt(0);
                packet.WriteByte(0x95);
                packet.WriteString("TestUser"); // Username
                packet.WriteByte(0);
                packet.WriteByte(0);
                packet.WriteLong(0);
                packet.WriteLong(128664365579370000); // create date

                packet.WriteInt(78);

                packet.WriteBool(false); // Else, 18 bytes
                packet.WriteBool(true); // Send 0x22 back
                packet.WriteByte(0);

                packet.WriteLong(0); // CC key

                this.SendPacket(packet);
            }
            else if (header == 0x0022)
            {
                Packet packet = new Packet(0x0009);
                byte worldid = 41;
                string worldname = "Ultimate";
                byte channels = 10;

                packet.WriteByte(worldid);
                packet.WriteString(worldname);
                packet.WriteByte(1);
                packet.WriteString("");
                packet.WriteUShort(100);
                packet.WriteUShort(100);

                packet.WriteByte(0);

                packet.WriteByte(channels); // Channels
                for (byte i = 0; i < channels; i++)
                {
                    packet.WriteString(string.Format("{0}-{1}", worldname, i + 1));
                    packet.WriteInt(1000);
                    packet.WriteByte(worldid);
                    packet.WriteByte(i);
                    packet.WriteByte(0);
                }

                packet.WriteShort(0); // Tooltip thingies

                packet.WriteInt(0);

                this.SendPacket(packet);

                packet = new Packet(0x0009);
                packet.WriteByte(0xFF);
                packet.WriteByte(0); // ? Restriction message?
                this.SendPacket(packet);

            }
            else if (header == 0x001D)
            {
                Packet packet = new Packet(0x0004);
                packet.WriteByte(0); // Server status
                packet.WriteByte(0);
                this.SendPacket(packet);
            }
            else
            {
                Console.WriteLine("Unknown");
            }
        }
    }
}
