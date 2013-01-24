using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class CharacterQuests
    {
        public Dictionary<ushort, string> Running { get; private set; }
        public Dictionary<ushort, long> Done { get; private set; }


        public void Decode(MaplePacket pPacket)
        {
            pPacket.ReadByte(); // ?


            Running = new Dictionary<ushort, string>();
            Done = new Dictionary<ushort, long>();

            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                Running.Add(pPacket.ReadUShort(), pPacket.ReadString());
            }

            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                pPacket.ReadString();
                pPacket.ReadString();
            }

            pPacket.ReadByte(); // ?

            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                Done.Add(pPacket.ReadUShort(), pPacket.ReadLong());
            }
        }
    }
}
