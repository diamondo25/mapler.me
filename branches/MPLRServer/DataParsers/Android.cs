using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public class Android
    {
        public int ID { get; private set; }

        public string Name { get; private set; }
        public byte Type { get; private set; }
        public byte Skin { get; private set; }
        public short Hair { get; private set; }
        public short Face { get; private set; }
        public int[] Equips { get; private set; }

        public void Decode(MaplePacket pPacket)
        {
            this.ID = pPacket.ReadInt();
            this.Type = pPacket.ReadByte();

            pPacket.ReadShort(); // X
            pPacket.ReadShort(); // Y
            pPacket.ReadByte(); // Stance
            pPacket.ReadShort(); // Foothold

            this.Skin = (byte)pPacket.ReadShort();
            this.Hair = pPacket.ReadShort();
            this.Face = pPacket.ReadShort();
            this.Name = pPacket.ReadString();

            this.Equips = new int[7];
            for (int i = 0; i < 7; i++)
                this.Equips[i] = pPacket.ReadInt();
        }

    }
}
