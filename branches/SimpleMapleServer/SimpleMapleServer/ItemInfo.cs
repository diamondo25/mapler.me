using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace SimpleMapleServer
{

    public class ItemBase
    {
        public int ItemID { get; set; }
        public long Expires { get; protected set; }
        // public long CashID { get; set; }
        public short Amount { get; set; }

        public int BagID { get; set; }

        public ItemBase(int pItemID)
        {
            this.ItemID = pItemID;
            this.Expires = 150842304000000000;

        }

        public virtual void Write(Packet pPacket)
        {
            byte type = 2;
            if (ItemID / 1000000 == 1) type = 1;
            else if (ItemID / 10000 == 500) type = 3; // Pet

            pPacket.WriteByte(type);
            pPacket.WriteInt(ItemID);

            pPacket.WriteBool(false); // No cash id

            pPacket.WriteLong(this.Expires); // Expire date

            pPacket.WriteInt(this.BagID); // Bag ID

        }
    }

    public class ItemEquip : ItemBase
    {
        public byte Slots { get; set; }
        public byte Scrolls { get; set; }
        public short Str { get; set; }
        public short Dex { get; set; }
        public short Int { get; set; }
        public short Luk { get; set; }
        public short HP { get; set; }
        public short MP { get; set; }
        public short Watk { get; set; }
        public short Matk { get; set; }
        public short Wdef { get; set; }
        public short Mdef { get; set; }
        public short Acc { get; set; }
        public short Avo { get; set; }
        public short Hands { get; set; }
        public short Jump { get; set; }
        public short Speed { get; set; }

        public string Name { get; set; }
        public ushort Flags { get; set; }

        public byte IncreasesSkills { get; set; }

        public byte ItemLevel { get; set; }
        public long ItemEXP { get; set; }

        public int Durability { get; set; }
        public int ViciousHammer { get; set; }
        public ushort BattleModeDamage { get; set; }

        public byte BossDamageRate { get; set; }
        public byte IgnoreDamageRate { get; set; }

        public byte MaxScissors { get; set; }



        public ushort StatusFlags { get; set; } // [XX, unk] [YY, amount of stars] | ?? 02 = 2 stars, ?? 0C = 12 stars!

        public ushort Potential1 { get; set; }
        public ushort Potential2 { get; set; }
        public ushort Potential3 { get; set; }
        // Bonus potentials
        public ushort Potential4 { get; set; }
        public ushort Potential5 { get; set; }
        public ushort Potential6 { get; set; }


        public ushort DisplayID { get; set; } // ID of anvilled item, 0 if not set. calculate with: (ItemID - (ItemID % 10000)) + DisplayID
        public ushort SocketState { get; set; } // 00 00 = No nebs, 03 00 = Neb 1 open, 13 00 = Neb 1 used (others closed)
        public short Nebulite1 { get; set; }
        public short Nebulite2 { get; set; } // Could be 2 other nebs...!?
        public short Nebulite3 { get; set; }

        public long UniqueID { get; set; }
        public string SetFlags { get; set; }

        public ItemEquip(int pItemID)
            : base(pItemID)
        {
            this.Name = "";
        }

        public override void Write(Packet pPacket)
        {
            base.Write(pPacket);

            {
                // flags 1
                uint flag = 0;
                int tmp = pPacket.Position;
                pPacket.WriteInt(0);

                // this\.([a-zA-Z]+) = FlaggedValue\(pConnection, this\.ItemID, flag, 0x([0-9]+), pPacket, this\.([a-zA-Z]+)\);
                // WriteFlaggedValue\(pPacket, 0x\2, this.\1, ref flag\);

                WriteFlaggedValue(pPacket, 0x01, this.Slots, ref flag);
                WriteFlaggedValue(pPacket, 0x02, this.Scrolls, ref flag);
                WriteFlaggedValue(pPacket, 0x04, this.Str, ref flag);
                WriteFlaggedValue(pPacket, 0x08, this.Dex, ref flag);
                WriteFlaggedValue(pPacket, 0x10, this.Int, ref flag);
                WriteFlaggedValue(pPacket, 0x20, this.Luk, ref flag);
                WriteFlaggedValue(pPacket, 0x40, this.HP, ref flag);
                WriteFlaggedValue(pPacket, 0x80, this.MP, ref flag);
                WriteFlaggedValue(pPacket, 0x100, this.Watk, ref flag);
                WriteFlaggedValue(pPacket, 0x200, this.Matk, ref flag);
                WriteFlaggedValue(pPacket, 0x400, this.Wdef, ref flag);
                WriteFlaggedValue(pPacket, 0x800, this.Mdef, ref flag);
                WriteFlaggedValue(pPacket, 0x1000, this.Acc, ref flag);
                WriteFlaggedValue(pPacket, 0x2000, this.Avo, ref flag);
                WriteFlaggedValue(pPacket, 0x4000, this.Hands, ref flag);
                WriteFlaggedValue(pPacket, 0x8000, this.Speed, ref flag);
                WriteFlaggedValue(pPacket, 0x10000, this.Jump, ref flag);
                WriteFlaggedValue(pPacket, 0x20000, this.Flags, ref flag);

                WriteFlaggedValue(pPacket, 0x40000, this.IncreasesSkills, ref flag);

                WriteFlaggedValue(pPacket, 0x80000, this.ItemLevel, ref flag);
                WriteFlaggedValue(pPacket, 0x100000, this.ItemEXP, ref flag);


                WriteFlaggedValue(pPacket, 0x200000, this.Durability, ref flag);
                WriteFlaggedValue(pPacket, 0x400000, this.ViciousHammer, ref flag);

                WriteFlaggedValue(pPacket, 0x800000, this.BattleModeDamage, ref flag);

                // FlaggedValue\(pConnection, this\.ItemID, flag, 0x([0-9]+), pPacket, \(([a-zA-Z]+)\)(0), true\);
                // WriteFlaggedValue\(pPacket, 0x\1, \(\2\)\3, ref flag\);

                WriteFlaggedValue(pPacket, 0x1000000, (byte)0, ref flag); // Item level changer?
                WriteFlaggedValue(pPacket, 0x2000000, (short)0, ref flag); // Enhancement buff?
                WriteFlaggedValue(pPacket, 0x4000000, (int)0, ref flag);
                WriteFlaggedValue(pPacket, 0x8000000, (byte)0, ref flag); // Required level changer?!
                WriteFlaggedValue(pPacket, 0x10000000, (byte)0, ref flag); // Yggdrasil Wisdom?
                WriteFlaggedValue(pPacket, 0x20000000, (byte)0, ref flag); // Final Strike?

                WriteFlaggedValue(pPacket, 0x40000000, this.BossDamageRate, ref flag);
                WriteFlaggedValue(pPacket, 0x80000000, this.IgnoreDamageRate, ref flag);

                pPacket.SetUInt(tmp, flag);
            }

            {
                // flags 2
                uint flag = 0;
                int tmp = pPacket.Position;
                pPacket.WriteInt(0);

                WriteFlaggedValue(pPacket, 0x01, (byte)0, ref flag); // Total damage?
                WriteFlaggedValue(pPacket, 0x02, (byte)0, ref flag); // All stats?
                WriteFlaggedValue(pPacket, 0x04, this.MaxScissors, ref flag);
                WriteFlaggedValue(pPacket, 0x08, (long)0, ref flag);
                WriteFlaggedValue(pPacket, 0x10, (int)0, ref flag);

                pPacket.SetUInt(tmp, flag);
            }

            pPacket.WriteString(this.Name);
            pPacket.WriteUShort(this.StatusFlags);


            pPacket.WriteUShort(this.Potential1);
            pPacket.WriteUShort(this.Potential2);
            pPacket.WriteUShort(this.Potential3);

            pPacket.WriteUShort(this.Potential4);
            pPacket.WriteUShort(this.Potential5);
            pPacket.WriteUShort(this.Potential6);

            pPacket.WriteUShort(this.DisplayID);

            pPacket.WriteUShort(this.SocketState);
            pPacket.WriteShort(this.Nebulite1);
            pPacket.WriteShort(this.Nebulite2);
            pPacket.WriteShort(this.Nebulite3);

            // If cash id
            pPacket.WriteLong(this.UniqueID);

            pPacket.WriteLong(0);
            pPacket.WriteInt(-1);

        }

        private void WriteFlaggedValue(Packet pPacket, uint pFlag, byte pValue, ref uint pOutputFlag)
        {
            if (pValue == 0) return;
            pPacket.WriteByte(pValue);
            pOutputFlag |= pFlag;
        }

        private void WriteFlaggedValue(Packet pPacket, uint pFlag, short pValue, ref uint pOutputFlag)
        {
            if (pValue == 0) return;
            pPacket.WriteShort(pValue);
            pOutputFlag |= pFlag;
        }

        private void WriteFlaggedValue(Packet pPacket, uint pFlag, ushort pValue, ref uint pOutputFlag)
        {
            if (pValue == 0) return;
            pPacket.WriteUShort(pValue);
            pOutputFlag |= pFlag;
        }

        private void WriteFlaggedValue(Packet pPacket, uint pFlag, int pValue, ref uint pOutputFlag)
        {
            if (pValue == 0) return;
            pPacket.WriteInt(pValue);
            pOutputFlag |= pFlag;
        }

        private void WriteFlaggedValue(Packet pPacket, uint pFlag, long pValue, ref uint pOutputFlag)
        {
            if (pValue == 0) return;
            pPacket.WriteLong(pValue);
            pOutputFlag |= pFlag;
        }
    }


    public class ItemRechargable : ItemBase
    {
        public string CraftName { get; set; }
        public short Flags { get; set; }
        public long UniqueID { get; set; }

        public ItemRechargable(int pItemID)
            : base(pItemID)
        {
            this.CraftName = "";
        }

        public override void Write(Packet pPacket)
        {
            base.Write(pPacket);

            pPacket.WriteShort(this.Amount);
            pPacket.WriteString(this.CraftName);
            pPacket.WriteShort(this.Flags);

            int itemtype = ItemID / 10000;
            if (itemtype == 233 || itemtype == 207 || itemtype == 287 || itemtype == 288 || itemtype == 289) // Stars, Bullets & Familiars
                pPacket.WriteLong(this.UniqueID);

        }
    }

    class ItemPet : ItemBase
    {
        public string Petname { get; set; }
        public byte Level { get; set; }
        public short Closeness { get; set; }
        public byte Fullness { get; set; }

        public ItemPet(int pItemID)
            : base(pItemID)
        {

        }

        public override void Write(Packet pPacket)
        {
            base.Write(pPacket);


            pPacket.WriteString(this.Petname, 13);
            pPacket.WriteByte(this.Level);
            pPacket.WriteShort(this.Closeness);
            pPacket.WriteByte(this.Fullness);

            pPacket.WriteLong(this.Expires);

            throw new NotImplementedException();
            //  pPacket.Skip(2 + 2 + 4 + 2 + 1 + 4 + 4 + 2);

            // Last 2:
            // - int: -1
            // - short: 100
            // V.132: + 2
        }
    }
}
