using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
   public class CharacterInventory
    {
        public class BagItem
        {
            public ItemBase InventoryItem { get; set; }
            public Dictionary<byte, ItemBase> Items { get; set; }
            public BagItem(ItemBase pItem)
            {
                InventoryItem = pItem;
                Items = new Dictionary<byte, ItemBase>();
            }
        }

#if LOCALE_GMS
        // V.129: +1 (Kanna/Hayato)
        public const byte EQUIP_INVENTORIES = 3 + 9 + 1; // 3 in main handler, 9 in other handler (noted as i >= 36) and 1 bits
#elif LOCALE_EMS
        public const byte EQUIP_INVENTORIES = 3 + 7 + 1; // 3 in main handler, 6 in other handler (noted as i >= 24) and 1 bits
#else // KMS?
        public const byte EQUIP_INVENTORIES = 3 + 10; // 3 in main handler, 9 in other handler (noted as i >= 36)
#endif
        public const byte NORMAL_INVENTORIES = 4;
        public const byte INVENTORIES = NORMAL_INVENTORIES + 1;

        public Dictionary<short, ItemEquip>[] EquipmentItems { get; private set; }
        public Dictionary<byte, ItemBase>[] InventoryItems { get; private set; }
        public Dictionary<int, BagItem> BagItems { get; private set; }
        public byte[] InventorySlots { get; private set; }

        public int[] TeleportRocks { get; private set; }

        public const int NORMAL_ROCKS = 5;
        public const int VIP_ROCKS = 10;
        public const int HYPER_ROCKS = 13;
        public const int HYPER_ROCKS2 = 13;

        public static byte GetEquipInventoryFromSlot(short pSlot)
        {
            if (pSlot > 100) // Impossible!
                pSlot = (short)-(pSlot);

            // MSB - 1
            // 0 = Normal Equips
            // 1 = Cash Equips. Note, same counting!
            // 2 = Inventory itself
            // 3 = Evan; Slot >= 1000
            // 4 = Mech; Slot >= 1100
            // 5 = Android; Slot >= 1200
            // 6 = Coordinate (AB); Slot >= 1300
            // 7 = Haku (Hayato and Kanna); Slot >= 1400
            // 8 = Bits in the case; Slot >= 1500
            // 9 = Totem; Slot >= 5000
            // 11 = Bits; Slot >= 20000
            if (pSlot > 0)
                return 2;
            else if (pSlot <= -20000)
                return 11;
            else if (pSlot <= -5000)
                return 9;
            else if (pSlot <= -1500)
                return 8;
            else if (pSlot <= -1400)
                return 7;
            else if (pSlot <= -1300)
                return 6;
            else if (pSlot <= -1200)
                return 5;
            else if (pSlot <= -1100)
                return 4;
            else if (pSlot <= -1000)
                return 3;
            else if (pSlot <= -100)
                return 1;
            // Slot <= 0
            return 0;
        }

        public static short CorrectEquipSlot(short pSlot)
        {
            return CorrectEquipSlot(GetEquipInventoryFromSlot(pSlot), pSlot);
        }

        public static short CorrectEquipSlot(byte pInternalInventory, short pSlot)
        {
            if (pSlot > 0)
                pSlot = (short)-pSlot; // Just to be sure

            switch (pInternalInventory)
            {
                case 0: return (short)-Math.Abs(pSlot);
                case 1:
                    if (pSlot > -100)
                        return (short)(pSlot - 100);
                    return pSlot;
                case 2:
                    return Math.Abs(pSlot); // Uses a normal slot
            }

            // all others these use -1N00 and lower
            return pSlot;
        }

        public void Decode(ClientConnection pConnection, MaplePacket pPacket)
        {
            InventorySlots = new byte[INVENTORIES];
            for (int i = 0; i < INVENTORIES; i++)
                InventorySlots[i] = pPacket.ReadByte();

            pPacket.ReadLong(); // 94354848000000000 | 1-1-1900



            EquipmentItems = new Dictionary<short, ItemEquip>[EQUIP_INVENTORIES];
#if LOCALE_EMS
            for (byte i = 0; i < 3; i++)
            {
                EquipmentItems[i] = new Dictionary<short, ItemEquip>();

                while (true)
                {
                    short slot = pPacket.ReadShort();
                    if (slot == 0) break;
                    slot = CharacterInventory.CorrectEquipSlot(i, slot);

                    ItemEquip equip = (ItemEquip)ItemBase.DecodeItemData(pConnection, pPacket);

                    EquipmentItems[i].Add(slot, equip);
                }
            }

            pPacket.ReadBool(); // EMS only -.-

            for (byte i = 3; i < EQUIP_INVENTORIES; i++)
            {
                EquipmentItems[i] = new Dictionary<short, ItemEquip>();

                while (true)
                {
                    short slot = pPacket.ReadShort();
                    if (slot == 0) break;
                    slot = CharacterInventory.CorrectEquipSlot(i, slot);

                    ItemEquip equip = (ItemEquip)ItemBase.DecodeItemData(pConnection, pPacket);

                    EquipmentItems[i].Add(slot, equip);
                }
            }
#else
            for (byte i = 0; i < EQUIP_INVENTORIES; i++)
            {
                EquipmentItems[i] = new Dictionary<short, ItemEquip>();

                while (true)
                {
                    short slot = pPacket.ReadShort();
                    if (slot == 0) break;
                    slot = CharacterInventory.CorrectEquipSlot(i, slot);

                    ItemEquip equip = (ItemEquip)ItemBase.DecodeItemData(pConnection, pPacket);

                    EquipmentItems[i].Add(slot, equip);
                }
            }
#endif

            InventoryItems = new Dictionary<byte, ItemBase>[NORMAL_INVENTORIES];
            BagItems = new Dictionary<int, BagItem>();

            for (byte i = 0; i < NORMAL_INVENTORIES; i++)
            {
                InventoryItems[i] = new Dictionary<byte, ItemBase>();

                while (true)
                {
                    byte slot = pPacket.ReadByte();
                    if (slot == 0) break;

                    ItemBase item = ItemBase.DecodeItemData(pConnection, pPacket);
                    InventoryItems[i].Add(slot, item);

                    if (item.BagID != -1)
                    {
                        // Update BagID... O.o
                        item.BagID = GameHelper.GetBagID(item.BagID, i);

                        BagItem bi = new BagItem(item);
                        BagItems.Add(item.BagID, bi);
                    }
                }
            }

            // Bagzzz
            for (int inv = 3; inv <= 4; inv++)
            {
                var bags = pPacket.ReadInt();
                for (int i = 0; i < bags; i++)
                {
                    int bagid = pPacket.ReadInt();

                    int bagitemid = pPacket.ReadInt();

                    BagItem bi = BagItems[GameHelper.GetBagID(bagid, inv - 2)]; // No addition to inv...!

                    while (true)
                    {
                        int slotid = pPacket.ReadInt();
                        if (slotid == -1) break;

                        ItemBase item = ItemBase.DecodeItemData(pConnection, pPacket);
                        bi.Items.Add((byte)slotid, item);
                    }
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

}
