using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class CharacterInventory
    {
        public const byte EQUIP_INVENTORIES = 8; // DAFUCK PEOPLE
        public const byte NORMAL_INVENTORIES = 4;
        public const byte INVENTORIES = NORMAL_INVENTORIES + 1;

        public Dictionary<short, ItemEquip>[] EquipmentItems { get; private set; }
        public Dictionary<byte, ItemBase>[] InventoryItems { get; private set; }
        public byte[] InventorySlots { get; private set; }

        public int[] TeleportRocks { get; private set; }

        public const int NORMAL_ROCKS = 5;
        public const int VIP_ROCKS = 10;
        public const int HYPER_ROCKS = 13;
        public const int HYPER_ROCKS2 = 13;

        public void Decode(MaplePacket pPacket)
        {
            InventorySlots = new byte[INVENTORIES];
            for (int i = 0; i < INVENTORIES; i++)
                InventorySlots[i] = pPacket.ReadByte();

            pPacket.ReadLong(); // 94354848000000000 | 1-1-1900



            EquipmentItems = new Dictionary<short, ItemEquip>[EQUIP_INVENTORIES];

            for (byte i = 0; i < EQUIP_INVENTORIES; i++)
            {
                EquipmentItems[i] = new Dictionary<short, ItemEquip>();

                while (true)
                {
                    short slot = pPacket.ReadShort();
                    if (slot == 0) break;

                    ItemEquip equip = (ItemEquip)ItemBase.DecodeItemData(pPacket);

                    EquipmentItems[i].Add(slot, equip);
                }
            }

            InventoryItems = new Dictionary<byte, ItemBase>[NORMAL_INVENTORIES];

            for (byte i = 0; i < NORMAL_INVENTORIES; i++)
            {
                InventoryItems[i] = new Dictionary<byte, ItemBase>();

                while (true)
                {
                    byte slot = pPacket.ReadByte();
                    if (slot == 0) break;

                    ItemBase item = ItemBase.DecodeItemData(pPacket);

                    InventoryItems[i].Add(slot, item);
                }
            }
        }

        public void DecodeTeleportRocks(MaplePacket pPacket)
        {
            TeleportRocks = new int[NORMAL_ROCKS + VIP_ROCKS + HYPER_ROCKS + HYPER_ROCKS2];
            int i = 0;
            for (; i < TeleportRocks.Length; i++)
            {
                TeleportRocks[i] = pPacket.ReadInt();
            }
        }
    }

    class ItemBase
    {
        public int ItemID { get; private set; }
        public long Expires { get; private set; }
        public long CashID { get; private set; }
        public short Amount { get; protected set; }

        public static ItemBase DecodeItemData(MaplePacket pPacket)
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

            ret.Decode(pPacket);

            return ret;
        }

        public virtual void Decode(MaplePacket pPacket)
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

            pPacket.ReadInt(); // -1?
        }

        public virtual int GetChecksum()
        {
            return ItemID + (int)CashID + Amount;
        }
    }

    class ItemEquip : ItemBase
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
        public short Flags { get; private set; }

        public byte Level { get; private set; }

        public int ViciousHammer { get; private set; }

        public short Potential1 { get; private set; }
        public short Potential2 { get; private set; }
        public short Potential3 { get; private set; }
        public short Potential4 { get; private set; }
        public short Potential5 { get; private set; }


        public short SocketState { get; private set; }
        public short Socket1 { get; private set; }
        public short Socket2 { get; private set; }
        public short Socket3 { get; private set; }

        public override void Decode(MaplePacket pPacket)
        {
            base.Decode(pPacket);


            this.Slots = pPacket.ReadByte();
            this.Scrolls = pPacket.ReadByte();
            this.Str = pPacket.ReadShort();
            this.Dex = pPacket.ReadShort();
            this.Int = pPacket.ReadShort();
            this.Luk = pPacket.ReadShort();
            this.HP = pPacket.ReadShort();
            this.MP = pPacket.ReadShort();
            this.Watk = pPacket.ReadShort();
            this.Matk = pPacket.ReadShort();
            this.Wdef = pPacket.ReadShort();
            this.Mdef = pPacket.ReadShort();
            this.Acc = pPacket.ReadShort();
            this.Avo = pPacket.ReadShort();
            this.Hands = pPacket.ReadShort();
            this.Speed = pPacket.ReadShort();
            this.Jump = pPacket.ReadShort();

            this.Name = pPacket.ReadString();
            this.Flags = pPacket.ReadShort();

            pPacket.ReadByte(); // Increases Skill
            this.Level = pPacket.ReadByte();

            pPacket.ReadInt();
            pPacket.ReadInt(); // 
            this.ViciousHammer = pPacket.ReadInt();

            pPacket.ReadShort(); // PVP damage

            pPacket.ReadByte();
            pPacket.ReadByte();

            this.Potential1 = pPacket.ReadShort();
            this.Potential2 = pPacket.ReadShort();
            this.Potential3 = pPacket.ReadShort();
            this.Potential4 = pPacket.ReadShort();
            this.Potential5 = pPacket.ReadShort();

            pPacket.ReadShort(); // New?

            this.SocketState = pPacket.ReadShort();
            this.Socket1 = pPacket.ReadShort();
            this.Socket2 = pPacket.ReadShort();
            this.Socket3 = pPacket.ReadShort();

            pPacket.ReadLong();

            pPacket.ReadLong();

            pPacket.ReadInt();
        }

        public override int GetChecksum()
        {
            return base.GetChecksum() + 
                Slots + Scrolls + Str + Dex + Int + Luk + HP + MP + 
                Watk + Wdef + Matk + Mdef + Acc + Avo + Hands + Jump + 
                Speed + Flags + ViciousHammer + 
                Potential1 + Potential2 + Potential3 + Potential4 + Potential5 + 
                SocketState + Socket1 + Socket2 + Socket3;
        }
    }

    class ItemRechargable : ItemBase
    {
        public string CraftName { get; private set; }
        public short Flags { get; private set; }
        public long UniqueID { get; private set; }

        public override void Decode(MaplePacket pPacket)
        {
            base.Decode(pPacket);

            Amount = pPacket.ReadShort();
            CraftName = pPacket.ReadString();
            Flags = pPacket.ReadShort();

            int itemtype = ItemID / 10000;
            if (itemtype == 233 || itemtype == 207)
                UniqueID = pPacket.ReadLong();
        }

        public override int GetChecksum()
        {
            return base.GetChecksum() + Flags;
        }
    }

    class ItemPet : ItemBase
    {
        public string Petname { get; private set; }
        public byte Level { get; private set; }
        public short Closeness { get; private set; }
        public byte Fullness { get; private set; }

        public override void Decode(MaplePacket pPacket)
        {
            base.Decode(pPacket);

            Petname = pPacket.ReadString(13);
            Level = pPacket.ReadByte();
            Closeness = pPacket.ReadShort();
            Fullness = pPacket.ReadByte();


            pPacket.Skip(8 + 2 + 2 + 4 + 2 + 1 + 4 + 4);
        }
    }
}
