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
        public Dictionary<ushort, string> PartyQuestsRunning { get; private set; }
        public Dictionary<ushort, long> PartyQuestsDone { get; private set; }


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

        public void DecodePQ(MaplePacket pPacket)
        {
            PartyQuestsRunning = new Dictionary<ushort, string>();
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                ushort id = pPacket.ReadUShort();
                string value = pPacket.ReadString();
                PartyQuestsRunning.Add(id, value);
            }
        }

        public void DecodePQDone(MaplePacket pPacket)
        {
            PartyQuestsDone = new Dictionary<ushort, long>();
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                ushort id = pPacket.ReadUShort();
                long time = pPacket.ReadLong();
                PartyQuestsDone.Add(id, time);
            }
        }
    }
}
