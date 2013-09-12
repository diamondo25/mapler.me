using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
   public class GW_CharacterStat
    {
        public int ID { get; private set; }
        public string Name { get; private set; }

        public byte Gender { get; set; }
        public byte Skin { get; set; }
        public int Face { get; set; }
        public int Hair { get; set; }

        public long[] Pets { get; private set; }

        public byte Level { get; set; }

        public short JobID { get; set; }
        public short Str { get; set; }
        public short Dex { get; set; }
        public short Int { get; set; }
        public short Luk { get; set; }
        public int HP { get; set; }
        public int MaxHP { get; set; }
        public int MP { get; set; }
        public int MaxMP { get; set; }

        public short AP { get; set; }
        public short SP { get; set; }

        public List<KeyValuePair<byte, int>> SPData { get; private set; }

        public long EXP { get; set; }
        public int Fame { get; set; }

        public int MapID { get; set; }
        public byte MapPos { get; set; }

        public int Unknown2 { get; private set; }
        public short JobSubID { get; private set; }

        public int DemonMark { get; private set; }
        public byte JobType { get; private set; }

        public int DateThing { get; private set; }

        public long Mesos { get; set; }

        public int[] Traits { get; private set; }
        public ushort[] TraitsToday { get; private set; }

        public int PVPExp { get; private set; }
        public byte PVPRank { get; private set; }
        public int BattlePoints { get; private set; }
        public byte BattleRank { get; private set; }

        public int HonourLevel { get; set; }
        public int HonourExp { get; set; }

        public enum TraitVals
        {
            Charisma = 0,
            Insight,
            Willpower,
            CraftDiligence,
            Empathy,
            Charm
        }

        public GW_CharacterStat()
        {
            SPData = null;
        }

        public void Decode(MaplePacket pPacket)
        {
            this.ID = pPacket.ReadInt();
            this.Name = pPacket.ReadString(13);

            Logger.WriteLine("FOUND CHARACTER {0} (ID: {1})", this.Name, this.ID);

            this.Gender = pPacket.ReadByte();
            this.Skin = pPacket.ReadByte();
            this.Face = pPacket.ReadInt();
            this.Hair = pPacket.ReadInt();

#if LOCALE_EMS
            this.Pets = new long[3] { 0, 0, 0 }; // Not defined!?
#else
            this.Pets = new long[3] { pPacket.ReadLong(), pPacket.ReadLong(), pPacket.ReadLong() };
#endif

            this.Level = pPacket.ReadByte();


            this.JobID = pPacket.ReadShort();
            this.Str = pPacket.ReadShort();
            this.Dex = pPacket.ReadShort();
            this.Int = pPacket.ReadShort();
            this.Luk = pPacket.ReadShort();
            this.HP = pPacket.ReadInt();
            this.MaxHP = pPacket.ReadInt();
            this.MP = pPacket.ReadInt();
            this.MaxMP = pPacket.ReadInt();

            this.AP = pPacket.ReadShort();

            SPData = new List<KeyValuePair<byte, int>>();
            if (GameHelper.IsExtendedSPJob(this.JobID))
            {
                byte amnt = pPacket.ReadByte();
                List<byte> haslist = new List<byte>();
                for (int j = 0; j < amnt; j++)
                {
                    byte v1 = pPacket.ReadByte(); // Job ID
                    int v2 = pPacket.ReadInt(); // Amount
                    SPData.Add(new KeyValuePair<byte, int>(v1, v2));

                    haslist.Add(v1);
                }
                for (byte j = 1; j < 20; j++)
                {
                    if (!haslist.Contains(j))
                        SPData.Add(new KeyValuePair<byte, int>(j, 0));
                }
            }
            else
            {
                SPData.Add(new KeyValuePair<byte, int>(0, pPacket.ReadShort()));
            }

            this.EXP = pPacket.ReadLong();
            this.Fame = pPacket.ReadInt();
#if LOCALE_GMS
            pPacket.ReadInt(); // Gacha EXP
            pPacket.ReadInt(); // V.141, unknown
#elif LOCALE_EMS
            pPacket.ReadLong();
            pPacket.ReadLong();
#endif
            this.MapID = pPacket.ReadInt();
            this.MapPos = pPacket.ReadByte();

#if LOCALE_GMS
            pPacket.ReadInt();
#endif
            this.JobSubID = pPacket.ReadShort();


            if (this.JobID / 100 == 31 || this.JobID / 100 == 36 || this.JobID == 3001 || this.JobID == 3002)
            {
                this.DemonMark = pPacket.ReadInt();
            }

            //this.JobType = pPacket.ReadByte();
            pPacket.ReadByte(); // Fatigue ?

            this.DateThing = pPacket.ReadInt(); // YYYYMMDDhh

            this.Traits = new int[6] { 
                pPacket.ReadInt(), // Charisma
                pPacket.ReadInt(), // Insight
                pPacket.ReadInt(), // Willpower
                pPacket.ReadInt(), // Craft/Diligence
                pPacket.ReadInt(), // Empathy
                pPacket.ReadInt()  // Charm
            };

            this.TraitsToday = new ushort[6] { 
                pPacket.ReadUShort(), // Charisma
                pPacket.ReadUShort(), // Insight
                pPacket.ReadUShort(), // Willpower
                pPacket.ReadUShort(), // Craft/Diligence
                pPacket.ReadUShort(), // Empathy
                pPacket.ReadUShort()  // Charm
            };


            pPacket.Skip(21 - 12); // Leftover: 9 bytes

            pPacket.ReadInt();

            pPacket.ReadByte();
            pPacket.ReadInt();
            pPacket.ReadByte();
            pPacket.ReadByte();
            pPacket.ReadInt();
            pPacket.ReadByte();

            pPacket.ReadInt();
            pPacket.ReadInt();

            pPacket.ReadInt();
            pPacket.ReadByte(); // != 0 check

            // List of Parttime jobs?
            for (int i = 1; i <= 9; i++)
            {
                pPacket.ReadInt(); // Character ID
                pPacket.ReadByte(); // Level
                pPacket.ReadInt(); // Job ID
            }

            pPacket.ReadInt();
            pPacket.ReadInt();

#if LOCALE_EMS
            pPacket.ReadInt();
#endif
        }

        public void DecodeMesos(MaplePacket pPacket)
        {
            // THIS FUNCTION IS HAAARD
            this.Mesos = pPacket.ReadLong();
            // Phew, that's done...
        }
    }
}
