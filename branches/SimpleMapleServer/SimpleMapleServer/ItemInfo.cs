using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Runtime.Serialization;

namespace SimpleMapleServer
{
    [DataContract]
    public class ItemBase
    {
        [DataMember]
        public int ItemID { get; set; }
        [DataMember]
        public long Expires { get; protected set; }
        // public long CashID {get;set;}[DataMember]
        public short Amount { get; set; }
        [DataMember]

        public int BagID { get; set; }

        public ItemBase()
        {
            this.Expires = 150842304000000000;
        }

        public ItemBase(int pItemID)
            : this()
        {
            this.ItemID = pItemID;
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

    [DataContract]
    public class ItemEquip : ItemBase
    {
        [DataMember]
        public byte Slots { get; set; }
        [DataMember]
        public byte Scrolls { get; set; }
        [DataMember]
        public short Str { get; set; }
        [DataMember]
        public short Dex { get; set; }
        [DataMember]
        public short Int { get; set; }
        [DataMember]
        public short Luk { get; set; }
        [DataMember]
        public short HP { get; set; }
        [DataMember]
        public short MP { get; set; }
        [DataMember]
        public short Watk { get; set; }
        [DataMember]
        public short Matk { get; set; }
        [DataMember]
        public short Wdef { get; set; }
        [DataMember]
        public short Mdef { get; set; }
        [DataMember]
        public short Acc { get; set; }
        [DataMember]
        public short Avo { get; set; }
        [DataMember]
        public short Hands { get; set; }
        [DataMember]
        public short Jump { get; set; }
        [DataMember]
        public short Speed { get; set; }
        [DataMember]

        public string Name { get; set; }
        [DataMember]
        public ushort Flags { get; set; }
        [DataMember]

        public byte IncreasesSkills { get; set; }
        [DataMember]

        public byte ItemLevel { get; set; }
        [DataMember]
        public long ItemEXP { get; set; }
        [DataMember]

        public int Durability { get; set; }
        [DataMember]
        public int ViciousHammer { get; set; }
        [DataMember]
        public ushort BattleModeDamage { get; set; }
        [DataMember]

        public byte EquipmentLevelReduction { get; set; }
        [DataMember]
        /// <summary>
        ///  - If Durability item: Durability value ingame will be multiplied (and value will be percentage): 100000%
        ///  - If non-Durability item: Durability will be negative percentage: -3%
        /// </summary>
        public int SecondDurability { get; set; }
        [DataMember]
        public byte BossDamageRate { get; set; }
        [DataMember]
        public byte IgnoreDamageRate { get; set; }
        [DataMember]

        public byte TotalDamagePercentage { get; set; }
        [DataMember]
        public byte AllStatsPercentage { get; set; }
        [DataMember]

        public byte MaxScissors { get; set; }
        [DataMember]



        public ushort StatusFlags { get; set; }
        [DataMember] // [XX, unk] [YY, amount of stars] | ?? 02 = 2 stars, ?? 0C = 12 stars!

        public ushort Potential1 { get; set; }
        [DataMember]
        public ushort Potential2 { get; set; }
        [DataMember]
        public ushort Potential3 { get; set; }
        // Bonus potentials
        [DataMember]
        public ushort Potential4 { get; set; }
        [DataMember]
        public ushort Potential5 { get; set; }
        [DataMember]
        public ushort Potential6 { get; set; }

        [DataMember]
        public ushort DisplayID { get; set; } // ID of anvilled item, 0 if not set. calculate with: (ItemID - (ItemID % 10000)) + DisplayID
        /// <summary>
        /// Enum:
        /// 0x0001 = Nebulite slot added
        /// 0x0002 = 1st socket open
        /// 0x0004 = 2nd socket open
        /// 0x0008 = 3rd socket open
        /// 0x0010 = 1st socket used
        /// 0x0020 = 2nd socket used
        /// 0x0040 = 3rd socket used
        /// </summary>
        [DataMember]
        public ushort SocketState { get; set; }
        
        [DataMember]
        public short Nebulite1 { get; set; }
        [DataMember]
        public short Nebulite2 { get; set; }
        [DataMember] // Could be 2 other nebs...!?
        public short Nebulite3 { get; set; }

        [DataMember]
        public long UniqueID { get; set; }
        [DataMember]
        public string SetFlags { get; set; }

        public ItemEquip(int pItemID)
            : base(pItemID)
        {
            this.Name = "";
            this.Str = 3;
            this.Dex = 2;
        }

        public override void Write(Packet pPacket)
        {
            base.Write(pPacket);

            if (this.SocketState != 0)
            {
                this.SocketState |= 0x0001;
            }

            {
                // flags 1
                uint flag = 0;
                int tmp = pPacket.Position;
                pPacket.WriteInt(0);

                // this\.([a-zA-Z]+) = FlaggedValue\(pConnection, this\.ItemID, flag, 0x([0-9]+), pPacket, this\.([a-zA-Z]+)\);
                // WriteFlaggedValue\(pPacket, 0x\2, this.\1, ref flag\);

                WriteFlaggedValue(pPacket, 0x00000001, this.Slots, ref flag);
                WriteFlaggedValue(pPacket, 0x00000002, this.Scrolls, ref flag);
                WriteFlaggedValue(pPacket, 0x00000004, this.Str, ref flag);
                WriteFlaggedValue(pPacket, 0x00000008, this.Dex, ref flag);
                WriteFlaggedValue(pPacket, 0x00000010, this.Int, ref flag);
                WriteFlaggedValue(pPacket, 0x00000020, this.Luk, ref flag);
                WriteFlaggedValue(pPacket, 0x00000040, this.HP, ref flag);
                WriteFlaggedValue(pPacket, 0x00000080, this.MP, ref flag);
                WriteFlaggedValue(pPacket, 0x00000100, this.Watk, ref flag);
                WriteFlaggedValue(pPacket, 0x00000200, this.Matk, ref flag);
                WriteFlaggedValue(pPacket, 0x00000400, this.Wdef, ref flag);
                WriteFlaggedValue(pPacket, 0x00000800, this.Mdef, ref flag);
                WriteFlaggedValue(pPacket, 0x00001000, this.Acc, ref flag);
                WriteFlaggedValue(pPacket, 0x00002000, this.Avo, ref flag);
                WriteFlaggedValue(pPacket, 0x00004000, this.Hands, ref flag);
                WriteFlaggedValue(pPacket, 0x00008000, this.Speed, ref flag);
                WriteFlaggedValue(pPacket, 0x00010000, this.Jump, ref flag);
                WriteFlaggedValue(pPacket, 0x00020000, this.Flags, ref flag);

                WriteFlaggedValue(pPacket, 0x00040000, this.IncreasesSkills, ref flag);

                WriteFlaggedValue(pPacket, 0x00080000, this.ItemLevel, ref flag);
                WriteFlaggedValue(pPacket, 0x00100000, this.ItemEXP, ref flag);


                WriteFlaggedValue(pPacket, 0x00200000, this.Durability, ref flag);
                WriteFlaggedValue(pPacket, 0x00400000, this.ViciousHammer, ref flag);

                WriteFlaggedValue(pPacket, 0x00800000, this.BattleModeDamage, ref flag);

                // FlaggedValue\(pConnection, this\.ItemID, flag, 0x([0-9]+), pPacket, \(([a-zA-Z]+)\)(0), true\);
                // WriteFlaggedValue\(pPacket, 0x\1, \(\2\)\3, ref flag\);

                WriteFlaggedValue(pPacket, 0x01000000, this.EquipmentLevelReduction, ref flag); // Item level changer?
                WriteFlaggedValue(pPacket, 0x02000000, (short)0, ref flag); // Enhancement buff?
                WriteFlaggedValue(pPacket, 0x04000000, this.SecondDurability, ref flag); // Second durability
                WriteFlaggedValue(pPacket, 0x08000000, (byte)0, ref flag); // Required level changer?!
                WriteFlaggedValue(pPacket, 0x10000000, (byte)0, ref flag); // Yggdrasil Wisdom? | If higher than 1, 'Success'
                WriteFlaggedValue(pPacket, 0x20000000, (byte)0, ref flag); // Final Strike? | If higher than 1, 'Success'

                WriteFlaggedValue(pPacket, 0x40000000, this.BossDamageRate, ref flag);
                WriteFlaggedValue(pPacket, 0x80000000, this.IgnoreDamageRate, ref flag);

                pPacket.SetUInt(tmp, flag);
            }

            {
                // flags 2
                uint flag = 0;
                int tmp = pPacket.Position;
                pPacket.WriteInt(0);

                WriteFlaggedValue(pPacket, 0x01, this.TotalDamagePercentage, ref flag);
                WriteFlaggedValue(pPacket, 0x02, this.AllStatsPercentage, ref flag);
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
        [DataMember]
        public short Flags { get; set; }
        [DataMember]
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
        [DataMember]
        public byte Level { get; set; }
        [DataMember]
        public short Closeness { get; set; }
        [DataMember]
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