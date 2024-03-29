﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Globalization;

namespace MPLRServer
{
    public class CharacterQuests
    {
        public Dictionary<ushort, string> Running { get; private set; }
        public Dictionary<ushort, long> Done { get; private set; }
        public Dictionary<ushort, string> PartyQuestsRunning { get; private set; }
        public Dictionary<ushort, long> PartyQuestsDone { get; private set; }

        CultureInfo provider = CultureInfo.InvariantCulture;

        private long DecodeTimeFromInt(ClientConnection pConnection, uint pValue)
        {

            long ft = 0;
            DateTime temp;
            if (DateTime.TryParseExact(pValue.ToString(), "yyMMddHHmm", provider, DateTimeStyles.None, out temp))
                ft = temp.ToFileTime();
            else
            {
                pConnection.Logger_WriteLine("Unable to parse {0} as date. GG nexon.", pValue);
                ft = 150842304000000000L; // GG Nexon.
            }

            return ft;
        }

        public void Decode(ClientConnection pConnection, MaplePacket pPacket)
        {
            var v = pPacket.ReadByte(); // ?


            Running = new Dictionary<ushort, string>();
            Done = new Dictionary<ushort, long>();

            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                Running.Add(pPacket.ReadUShort(), pPacket.ReadString());
            }

            if (v == 0)
            {
                for (int i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadShort(); // UNK lol
                }
            }

            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                pPacket.ReadString(); // 1NX1702337
                pPacket.ReadString(); // '1' or '0 ' ?!
            }

            var hurr = pPacket.ReadByte(); // ?

            // ADDED IN v.128 !!!!
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                // New method of creating dates...
                var id = pPacket.ReadUShort();
                var date = pPacket.ReadUInt();

                long ft = DecodeTimeFromInt(pConnection, date);

                if (!Done.ContainsKey(id))
                    Done.Add(id, ft);
                else
                    pConnection.Logger_WriteLine("Duplicate Quest (Done): {0}", id);

            }

            if (hurr == 0)
            {
                for (int i = pPacket.ReadShort(); i > 0; i--)
                {
                    Done.Add(pPacket.ReadUShort(), pPacket.ReadLong());
                }
            }
        }

        public void DecodePQ(ClientConnection pConnection, MaplePacket pPacket)
        {
            PartyQuestsRunning = new Dictionary<ushort, string>();
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                ushort id = pPacket.ReadUShort();
                string value = pPacket.ReadString();
                if (!PartyQuestsRunning.ContainsKey(id))
                    PartyQuestsRunning.Add(id, value);
                else
                    pConnection.Logger_WriteLine("Duplicate PQ (Running): {0}", id);
            }
        }

        public void DecodePQDone(ClientConnection pConnection, MaplePacket pPacket)
        {
            PartyQuestsDone = new Dictionary<ushort, long>();
            for (int i = pPacket.ReadShort(); i > 0; i--)
            {
                ushort id = pPacket.ReadUShort();
#if LOCALE_EMS
                var date = pPacket.ReadUInt();

                long time = DecodeTimeFromInt(pConnection, date);
#else
                long time = pPacket.ReadLong();
#endif
                if (!PartyQuestsDone.ContainsKey(id))
                    PartyQuestsDone.Add(id, time);
                else
                    pConnection.Logger_WriteLine("Duplicate PQ (Done): {0}", id);
            }
        }
    }
}
