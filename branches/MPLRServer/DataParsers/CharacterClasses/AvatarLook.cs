using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
   public class AvatarLook
    {
        public Dictionary<byte, int>[] Equips { get; private set; }
        public byte Gender { get; private set; }
        public byte Skin { get; private set; }
        public int Face { get; private set; }
        public int Hair { get; private set; }

        public int JobID { get; private set; }

        public int Wings { get; private set; }


        public void Decode(MaplePacket pPacket)
        {
            this.Gender = pPacket.ReadByte(); // Gender
            this.Skin = pPacket.ReadByte(); // Skin
            this.Face = pPacket.ReadInt(); // Face

            this.JobID = pPacket.ReadInt(); // Job ID

            pPacket.ReadByte(); // First slot; hair
               this.Hair = pPacket.ReadInt(); // Hair ID

            Equips = new Dictionary<byte, int>[3];
            Equips[0] = new Dictionary<byte, int>();
            Equips[1] = new Dictionary<byte, int>();
            Equips[2] = new Dictionary<byte, int>();

            while (true)
            {
                byte slot = pPacket.ReadByte();
                if (slot == 0xFF) break;
                Equips[0].Add(slot, pPacket.ReadInt());
            }
            while (true)
            {
                byte slot = pPacket.ReadByte();
                if (slot == 0xFF) break;
                Equips[1].Add(slot, pPacket.ReadInt());
            }
            while (true)
            {
                byte slot = pPacket.ReadByte();
                if (slot == 0xFF) break;
                Equips[2].Add(slot, pPacket.ReadInt());
            }

            pPacket.ReadInt();
            pPacket.ReadInt();
            pPacket.ReadInt();

            pPacket.ReadByte();
            pPacket.ReadInt();
            pPacket.ReadInt();
            pPacket.ReadInt();

            if (this.JobID / 100 == 31 || this.JobID / 100 == 36 || this.JobID == 3001 || this.JobID == 3002)
            {
                this.Wings = pPacket.ReadInt();
            }
        }
    }
}
