using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{

    public class ItemBase
    {
        public int ItemID { get; private set; }
        public long Expires { get; protected set; }
        public long CashID { get; private set; }
        public short Amount { get; set; }

        public int BagID { get; set; }

        public static ItemBase DecodeItemData(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            ItemBase ret = null;
            switch (type)
            {
                case 1:
                    ret = new ItemEquip();
                    ret.Amount = 1;
                    break;
                case 2: ret = new ItemRechargable(); break;
                case 3:
                    ret = new ItemPet();
                    ret.Amount = 1;
                    break;
                default:
                    {
                        Logger.WriteLine("Unkown ItemType: {0}", type);
                        return null;
                    }
            }

            ret.Decode(pConnection, pPacket);

            return ret;
        }

        public virtual void Decode(ClientConnection pConnection, MaplePacket pPacket)
        {
            ItemID = pPacket.ReadInt();

            if (pPacket.ReadBool())
            {
                CashID = pPacket.ReadLong();
            }
            else
            {
                CashID = 0;
            }

            Expires = pPacket.ReadLong();

            BagID = pPacket.ReadInt();
        }

        public virtual int GetChecksum()
        {
            return ItemID + (int)CashID + Amount + (int)Expires + (int)(Expires >> 32) + BagID;
        }
    }

    public class ItemEquip : ItemBase
    {
        public byte Slots { get; private set; }
        public byte Scrolls { get; private set; }
        public short Str { get; private set; }
        public short Dex { get; private set; }
        public short Int { get; private set; }
        public short Luk { get; private set; }
        public short HP { get; private set; }
        public short MP { get; private set; }
        public short Watk { get; private set; }
        public short Matk { get; private set; }
        public short Wdef { get; private set; }
        public short Mdef { get; private set; }
        public short Acc { get; private set; }
        public short Avo { get; private set; }
        public short Hands { get; private set; }
        public short Jump { get; private set; }
        public short Speed { get; private set; }

        public string Name { get; private set; }
        public ushort Flags { get; private set; }

        public byte IncreasesSkills { get; private set; }

        public byte ItemLevel { get; private set; }
        public long ItemEXP { get; private set; }

        public int Durability { get; private set; }
        public int ViciousHammer { get; private set; }
        public ushort BattleModeDamage { get; private set; }

        public byte MaxScissors { get; private set; }



        public ushort StatusFlags { get; private set; } // [XX, unk] [YY, amount of stars] | ?? 02 = 2 stars, ?? 0C = 12 stars!

        public ushort Potential1 { get; private set; }
        public ushort Potential2 { get; private set; }
        public ushort Potential3 { get; private set; }
        // Bonus potentials
        public ushort Potential4 { get; private set; }
        public ushort Potential5 { get; private set; }
        public ushort Potential6 { get; private set; }


        public ushort DisplayID { get; private set; } // ID of anvilled item, 0 if not set. calculate with: (ItemID - (ItemID % 10000)) + DisplayID
        public ushort SocketState { get; private set; } // 00 00 = No nebs, 03 00 = Neb 1 open, 13 00 = Neb 1 used (others closed)
        public short Nebulite1 { get; private set; }
        public short Nebulite2 { get; private set; } // Could be 2 other nebs...!?
        public short Nebulite3 { get; private set; }

        public long UniqueID { get; private set; }
        public string SetFlags { get; private set; }


        public override void Decode(ClientConnection pConnection, MaplePacket pPacket)
        {
            base.Decode(pConnection, pPacket);


            List<string> flagsList = new List<string>();
            {
                {
                    uint flag = pPacket.ReadUInt();

                    this.Slots = FlaggedValue(pConnection, this.ItemID, flag, 0x01, pPacket, this.Slots);
                    this.Scrolls = FlaggedValue(pConnection, this.ItemID, flag, 0x02, pPacket, this.Scrolls);
                    this.Str = FlaggedValue(pConnection, this.ItemID, flag, 0x04, pPacket, this.Str);
                    this.Dex = FlaggedValue(pConnection, this.ItemID, flag, 0x08, pPacket, this.Dex);
                    this.Int = FlaggedValue(pConnection, this.ItemID, flag, 0x10, pPacket, this.Int);
                    this.Luk = FlaggedValue(pConnection, this.ItemID, flag, 0x20, pPacket, this.Luk);
                    this.HP = FlaggedValue(pConnection, this.ItemID, flag, 0x40, pPacket, this.HP);
                    this.MP = FlaggedValue(pConnection, this.ItemID, flag, 0x80, pPacket, this.MP);
                    this.Watk = FlaggedValue(pConnection, this.ItemID, flag, 0x100, pPacket, this.Watk);
                    this.Matk = FlaggedValue(pConnection, this.ItemID, flag, 0x200, pPacket, this.Matk);
                    this.Wdef = FlaggedValue(pConnection, this.ItemID, flag, 0x400, pPacket, this.Wdef);
                    this.Mdef = FlaggedValue(pConnection, this.ItemID, flag, 0x800, pPacket, this.Mdef);
                    this.Acc = FlaggedValue(pConnection, this.ItemID, flag, 0x1000, pPacket, this.Acc);
                    this.Avo = FlaggedValue(pConnection, this.ItemID, flag, 0x2000, pPacket, this.Avo);
                    this.Hands = FlaggedValue(pConnection, this.ItemID, flag, 0x4000, pPacket, this.Hands);
                    this.Speed = FlaggedValue(pConnection, this.ItemID, flag, 0x8000, pPacket, this.Speed);
                    this.Jump = FlaggedValue(pConnection, this.ItemID, flag, 0x10000, pPacket, this.Jump);
                    this.Flags = FlaggedValue(pConnection, this.ItemID, flag, 0x20000, pPacket, this.Flags);

                    this.IncreasesSkills = FlaggedValue(pConnection, this.ItemID, flag, 0x40000, pPacket, this.IncreasesSkills);

                    this.ItemLevel = FlaggedValue(pConnection, this.ItemID, flag, 0x80000, pPacket, this.ItemLevel);
                    this.ItemEXP = FlaggedValue(pConnection, this.ItemID, flag, 0x100000, pPacket, this.ItemEXP);


                    this.Durability = FlaggedValue(pConnection, this.ItemID, flag, 0x200000, pPacket, this.Durability);
                    this.ViciousHammer = FlaggedValue(pConnection, this.ItemID, flag, 0x400000, pPacket, this.ViciousHammer);

                    this.BattleModeDamage = FlaggedValue(pConnection, this.ItemID, flag, 0x800000, pPacket, this.BattleModeDamage);

                    FlaggedValue(pConnection, this.ItemID, flag, 0x1000000, pPacket, (byte)0, true); // Item level changer?
                    FlaggedValue(pConnection, this.ItemID, flag, 0x2000000, pPacket, (short)0, true); // Enhancement buff?
                    FlaggedValue(pConnection, this.ItemID, flag, 0x4000000, pPacket, (int)0, true);
                    FlaggedValue(pConnection, this.ItemID, flag, 0x8000000, pPacket, (byte)0, true); // Required level changer?!
                    FlaggedValue(pConnection, this.ItemID, flag, 0x10000000, pPacket, (byte)0, true); // Yggdrasil Wisdom?
                    FlaggedValue(pConnection, this.ItemID, flag, 0x20000000, pPacket, (byte)0, true); // Final Strike?
                    FlaggedValue(pConnection, this.ItemID, flag, 0x40000000, pPacket, (byte)0, true); // Boss Damage %?
                    FlaggedValue(pConnection, this.ItemID, flag, 0x80000000, pPacket, (byte)0, true); // Ignore Damage %?
                }

                {
                    uint flag = pPacket.ReadUInt();
                    FlaggedValue(pConnection, this.ItemID, flag, 0x01, pPacket, (byte)0, true); // Total damage?
                    FlaggedValue(pConnection, this.ItemID, flag, 0x02, pPacket, (byte)0, true); // All stats?
                    this.MaxScissors = FlaggedValue(pConnection, this.ItemID, flag, 0x04, pPacket, this.MaxScissors);
                    FlaggedValue(pConnection, this.ItemID, flag, 0x08, pPacket, (long)0, true);
                    FlaggedValue(pConnection, this.ItemID, flag, 0x10, pPacket, (int)0, true);
                }
            }

            this.Name = pPacket.ReadString();

            this.StatusFlags = pPacket.ReadUShort(); // Actually 2 bytes

            this.Potential1 = pPacket.ReadUShort();
            this.Potential2 = pPacket.ReadUShort();
            this.Potential3 = pPacket.ReadUShort();

            this.Potential4 = pPacket.ReadUShort();
            this.Potential5 = pPacket.ReadUShort();
            this.Potential6 = pPacket.ReadUShort();

            this.DisplayID = pPacket.ReadUShort();

#if LOCALE_GMS
            this.SocketState = pPacket.ReadUShort();
            this.Nebulite1 = pPacket.ReadShort();
            this.Nebulite2 = pPacket.ReadShort();
            this.Nebulite3 = pPacket.ReadShort();
#else
            pPacket.ReadShort();
#endif

            if (CashID == 0)
                this.UniqueID = pPacket.ReadLong();
            else
                this.UniqueID = 0;

            pPacket.ReadLong(); // Some weird expiration time


            pPacket.ReadInt(); // always -1?

#if (LOCALE_EMS || LOCALE_GMS)
            // Prolly RED patch related
            pPacket.ReadLong();
            pPacket.ReadLong(); // This is 94354848000000000; a default date for no expiration. 
            pPacket.ReadInt();
            for (int i = 0; i < 3; i++)
                pPacket.ReadInt();

#endif

            // Fix setflags
            if ((this.Flags & 0x80) == 0x80 && this.Name != "")
            {
                flagsList.Add("crafted");
            }

            this.SetFlags = string.Join(",", flagsList);
        }

        public override int GetChecksum()
        {
            return base.GetChecksum() +
                Slots + Scrolls + Str + Dex + Int + Luk + HP + MP +
                Watk + Wdef + Matk + Mdef + Acc + Avo + Hands + Jump +
                Speed + Flags + ViciousHammer +
                ItemLevel + (int)ItemEXP + (int)(ItemEXP >> 32) + 1 + BattleModeDamage +
                StatusFlags +
                Potential1 + Potential2 + Potential3 + Potential4 + Potential5 + Potential6 +
                DisplayID + SocketState + Nebulite1 +
                Nebulite2 + Nebulite3 +
                (int)UniqueID + (int)(UniqueID >> 32) + 1;
        }

        private static byte FlaggedValue(ClientConnection pConnection, int pItemID, uint pValue, uint pFlag, MaplePacket pPacket, byte pTypeValue, bool pLogIfFound = false)
        {
            if (pValue.HasFlag(pFlag))
            {
                var val = pPacket.ReadByte();
                if (pLogIfFound)
                    pConnection.Logger_WriteLine("Found flag {3} {0:X8}: {1} | ItemID: {2}", pFlag, val, pItemID, pTypeValue.GetType().Name);
                return val;
            }
            else
                return 0;
        }

        private static short FlaggedValue(ClientConnection pConnection, int pItemID, uint pValue, uint pFlag, MaplePacket pPacket, short pTypeValue, bool pLogIfFound = false)
        {
            if (pValue.HasFlag(pFlag))
            {
                var val = pPacket.ReadShort();
                if (pLogIfFound)
                    pConnection.Logger_WriteLine("Found flag {3} {0:X8}: {1} | ItemID: {2}", pFlag, val, pItemID, pTypeValue.GetType().Name);
                return val;
            }
            else
                return 0;
        }

        private static ushort FlaggedValue(ClientConnection pConnection, int pItemID, uint pValue, uint pFlag, MaplePacket pPacket, ushort pTypeValue, bool pLogIfFound = false)
        {
            if (pValue.HasFlag(pFlag))
            {
                var val = pPacket.ReadUShort();
                if (pLogIfFound)
                    pConnection.Logger_WriteLine("Found flag {3} {0:X8}: {1} | ItemID: {2}", pFlag, val, pItemID, pTypeValue.GetType().Name);
                return val;
            }
            else
                return 0;
        }

        private static int FlaggedValue(ClientConnection pConnection, int pItemID, uint pValue, uint pFlag, MaplePacket pPacket, int pTypeValue, bool pLogIfFound = false)
        {
            if (pValue.HasFlag(pFlag))
            {
                var val = pPacket.ReadInt();
                if (pLogIfFound)
                    pConnection.Logger_WriteLine("Found flag {3} {0:X8}: {1} | ItemID: {2}", pFlag, val, pItemID, pTypeValue.GetType().Name);
                return val;
            }
            else
                return 0;
        }

        private static long FlaggedValue(ClientConnection pConnection, int pItemID, uint pValue, uint pFlag, MaplePacket pPacket, long pTypeValue, bool pLogIfFound = false)
        {
            if (pValue.HasFlag(pFlag))
            {
                var val = pPacket.ReadLong();
                if (pLogIfFound)
                    pConnection.Logger_WriteLine("Found flag {3} {0:X8}: {1} | ItemID: {2}", pFlag, val, pItemID, pTypeValue.GetType().Name);
                return val;
            }
            else
                return 0;
        }
    }

    public class ItemRechargable : ItemBase
    {
        public string CraftName { get; private set; }
        public short Flags { get; private set; }
        public long UniqueID { get; private set; }

        public override void Decode(ClientConnection pConnection, MaplePacket pPacket)
        {
            base.Decode(pConnection, pPacket);

            Amount = pPacket.ReadShort();
            CraftName = pPacket.ReadString();
            Flags = pPacket.ReadShort();

            int itemtype = ItemID / 10000;
            if (itemtype == 233 || itemtype == 207 || itemtype == 287 || itemtype == 288 || itemtype == 289) // Stars, Bullets & Familiars
                UniqueID = pPacket.ReadLong();
        }

        public override int GetChecksum()
        {
            return base.GetChecksum() + Flags + (int)UniqueID + (int)(UniqueID >> 32);
        }
    }

    public class ItemPet : ItemBase
    {
        public string Petname { get; private set; }
        public byte Level { get; private set; }
        public short Closeness { get; private set; }
        public byte Fullness { get; private set; }

        public override void Decode(ClientConnection pConnection, MaplePacket pPacket)
        {
            base.Decode(pConnection, pPacket);

            Petname = pPacket.ReadString(13);
            Level = pPacket.ReadByte();
            Closeness = pPacket.ReadShort();
            Fullness = pPacket.ReadByte();

            this.Expires = pPacket.ReadLong();
            pPacket.Skip(2 + 2 + 4 + 2 + 1 + 4 + 4 + 2);
            // Last 2:
            // - int: -1
            // - short: 100
            // V.132: + 2
        }
    }
}