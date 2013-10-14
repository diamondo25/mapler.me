using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace SimpleMapleServer
{
    class ServerPackets
    {
        public static void EnterMapPacket(Session pSession, bool pConnecting, int pToMapID)
        {
            Packet packet = new Packet(Program.CHANGE_MAP_HEADER);
            {
                // Option list
                packet.WriteShort(2); // Amount

                packet.WriteInt(1);
                packet.WriteInt(0);

                packet.WriteInt(2);
                packet.WriteInt(0);
            }
            packet.WriteInt(12); // Channel

            packet.WriteByte(0);
            packet.WriteInt(0);

            packet.WriteByte(1); // Portals

            packet.WriteInt(0); // ...


            packet.WriteBool(pConnecting); // Is Connecting

            if (true)
            {
                packet.WriteShort(1); // Lines
                packet.WriteString("Test Server"); // Title
                packet.WriteString("This is a test server!");
            }
            else
            {
                packet.WriteShort(0);
            }

            if (pConnecting)
            {
                // Random Numbers for damage calculation
                packet.WriteUInt(4078638766);
                packet.WriteUInt(1285450110);
                packet.WriteUInt(2894412159);

                {
                    // Char Data
                    ulong flag = 0xFFDFFFFFFFFFFFFF;

                    packet.WriteULong(flag);
                    packet.WriteByte(0);


                    packet.WriteInt(0);
                    packet.WriteInt(0);
                    packet.WriteInt(0);

                    packet.WriteByte(0); // Loop1

                    packet.WriteInt(0); // Loop 2

                    packet.WriteByte(0);

                    packet.WriteBool(false);

                    {
                        // Stats

                        packet.WriteInt(1337); // ID
                        packet.WriteString("TestBoooii", 13);

                        packet.WriteByte(0);
                        packet.WriteByte(3);
                        packet.WriteInt(20100);
                        packet.WriteInt(33830);

                        // Pets
                        packet.WriteLong(0);
                        packet.WriteLong(0);
                        packet.WriteLong(0);

                        packet.WriteByte(250); // Level

                        short job = 910; // 910 = SGM
                        packet.WriteShort(job);

                        packet.WriteShort(1000); // STR
                        packet.WriteShort(2000); // DEX
                        packet.WriteShort(3000); // INT
                        packet.WriteShort(4000); // LUK

                        packet.WriteInt(500000); // HP
                        packet.WriteInt(500000); // Max HP
                        packet.WriteInt(500000); // MP
                        packet.WriteInt(500000); // Max MP

                        packet.WriteShort(0); // AP
                        if (IsExtendedSPJob(job)) packet.WriteByte(0);
                        else packet.WriteShort(0);

                        packet.WriteLong(123456789); // EXP
                        packet.WriteInt(0xBADF00D); // Fame

                        packet.WriteInt(10000); // Gacha EXP
                        packet.WriteInt(0); // V.141 unk

                        packet.WriteInt(pToMapID); // GM map
                        packet.WriteByte(0); // Map pos

                        packet.WriteInt(0); // Unk?

                        packet.WriteShort(0); // Job sub ID?

                        if (job / 100 == 31 || job / 100 == 36 || job == 3001 || job == 3002)
                        {
                            packet.WriteInt(1012278);
                        }

                        packet.WriteByte(0); // Fatigue

                        packet.WriteInt(int.Parse(DateTime.Now.ToString("yyyyMMddHH")));

                        // Traits
                        for (int i = 0; i < 6; i++)
                            packet.WriteInt(100 * i);

                        // Traits today
                        for (int i = 0; i < 6; i++)
                            packet.WriteUShort((ushort)(50 * i));

                        // 9 bytes leftover
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteByte(0);


                        packet.WriteInt(0);
                        packet.WriteByte(0);
                        packet.WriteInt(0);
                        packet.WriteByte(0);
                        packet.WriteByte(0);
                        packet.WriteInt(0);
                        packet.WriteByte(0);

                        packet.WriteInt(0);
                        packet.WriteInt(0);

                        packet.WriteInt(0);

                        packet.WriteByte(0);

                        // Parttime jobs
                        for (int i = 1; i <= 9; i++)
                        {

                            packet.WriteInt(0);
                            packet.WriteByte(0);
                            packet.WriteInt(0);
                        }


                        packet.WriteInt(0); // 'reverse' time!
                        packet.WriteInt(0);
                    }


                    packet.WriteByte(100); // Buddylist size

                    packet.WriteBool(false); // Blessings
                    packet.WriteBool(false);
                    packet.WriteBool(false);

                    packet.WriteLong(133713371337); // Mesos

                    packet.WriteInt(0); // Some loop
                    packet.WriteInt(0); // Magic Potions loop

                    {
                        // Inventories

                        for (int i = 0; i < 5; i++)
                            packet.WriteByte(8 * 10); // inventory slots

                        packet.WriteLong(94354848000000000); // Some say it's a date for a quest. I don't believe them

                        // Equips
                        for (int i = 0; i < 3 + 9 + 1; i++)
                        {
                            packet.WriteShort(0); // No items!
                        }

                        // Regular inventories
                        for (int i = 0; i < 4; i++)
                        {
                            packet.WriteByte(0); // No items!
                        }

                        // Bags
                        for (int i = 3; i <= 4; i++)
                        {
                            packet.WriteInt(0);
                        }
                    }

                    packet.WriteInt(0); // Loopies
                    packet.WriteInt(0);

                    // while loop here
                    packet.WriteByte(0); // Stop while loop.

                    {
                        // Skills

                        packet.WriteBool(true);
                        if (true)
                        {
                            packet.WriteShort(0); // No skills
                            packet.WriteShort(0); // No link skills
                        }
                        else
                        {
                            // I aint going to program this..
                        }

                        packet.WriteShort(0); // No cooldowns either
                    }

                    {
                        // Quests
                        bool wut = false;
                        packet.WriteBool(wut);
                        packet.WriteShort(0);

                        if (!wut)
                            packet.WriteShort(0);

                        packet.WriteShort(0);

                        wut = false;
                        packet.WriteBool(wut);
                        packet.WriteShort(0);

                        if (!wut)
                            packet.WriteShort(0);
                    }

                    packet.WriteShort(0); // Match records

                    {
                        // Bunch o rings

                        packet.WriteShort(0); // Couple
                        packet.WriteShort(0); // Friend
                        packet.WriteShort(0); // Marriage
                    }

                    // Tele rocks
                    for (int i = 0; i < 5 + 10 + 13 + 13; i++)
                        packet.WriteInt(999999999);


                    {
                        // Monsterbook

                        packet.WriteInt(0); // Chosen card
                        packet.WriteBool(false); // Method to use; false = old loop
                        packet.WriteShort(0);
                    }

                    packet.WriteInt(-1); // ?

                    packet.WriteShort(0);

                    // Newyear cards
                    packet.WriteShort(0);

                    // PQ data
                    packet.WriteShort(0);

                    // Wildhunter info... no
                    if (false)
                    {
                        packet.WriteByte(1);
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                    }

                    // Finished PQs
                    packet.WriteShort(0);

                    // Some event stuff
                    packet.WriteShort(0);

                    // Stolen skills
                    for (int i = 13; i > 0; i--)
                        packet.WriteInt(0);

                    // Chosen skills?
                    for (int i = 4; i > 0; i--)
                        packet.WriteInt(0);

                    // Inner stats
                    packet.WriteShort(0);

                    {
                        // Unknown stuff
                        packet.WriteInt(0);

                        packet.WriteByte(0);
                    }


                    // Honour info
                    packet.WriteInt(1337); // Level
                    packet.WriteInt(0); // EXP

                    {
                        // More unknown stuffs :(
                        packet.WriteByte(1); // Use first loop
                        packet.WriteShort(0); // End loop
                    }

                    packet.WriteBool(false); // Item decode if true

                    {
                        packet.WriteInt(1680023);
                        packet.WriteInt(1680024);
                        packet.WriteInt(1680025);
                        packet.WriteByte(1);
                    }

                    {
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteLong(94354848000000000);
                    }

                    {
                        // Evolution Cores
                        packet.WriteShort(0);
                        packet.WriteShort(0);
                    }

                    packet.WriteByte(0);

                    {
                        // Farm info
                        packet.WriteString("Creating...");
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteInt(0);

                        packet.WriteByte(0);

                        packet.WriteInt(0);
                        packet.WriteInt(0);
                        packet.WriteInt(0);

                        packet.WriteInt(0);
                        packet.WriteInt(0);
                    }

                    packet.WriteBool(false); // More item decode thingies

                    {
                        packet.WriteInt(0);
                        packet.WriteLong(94354848000000000);
                        packet.WriteInt(0);
                    }

                    {
                        // Decodes 84 bytes

                        packet.WriteInt(1257960);
                        // 4
                        for (int i = 0; i < 64; i += 8)
                            packet.WriteLong(0);
                        // 68
                        packet.WriteShort(0);
                        packet.WriteByte(0);
                        // 71

                        // Ohkay
                        packet.WriteLong(DateTime.Now.ToFileTime());
                        // 79

                        packet.WriteInt(0);
                        packet.WriteByte(1);
                        // 84
                    }

                    packet.WriteByte(0);

                    // loop...
                    packet.WriteShort(0);

                    {
                        packet.WriteInt(9995561);
                        packet.WriteInt(2027067);
                        packet.WriteInt(4);
                        packet.WriteInt(0);

                        for (int i = 0; i < 4; i++)
                        {
                            packet.WriteInt(9410165 + i);
                            packet.WriteInt(i * 1000);
                        }
                    }

                    {
                        // Bulk info
                        packet.WriteInt(0);
                    }
                }
            }
            else
            {
                packet.WriteByte(0);

                packet.WriteInt(pToMapID); // Mapid
                packet.WriteByte(0); // Mapppos

                packet.WriteInt(13371337); // HP

                packet.WriteBool(false);
            }


            packet.WriteLong(DateTime.Now.ToFileTime());
            packet.WriteInt(100);
            packet.WriteByte(0);
            packet.WriteByte(0);
            packet.WriteByte(1);

            pSession.SendPacket(packet);
        }

        public static void SendTestItem(Session pSession, int pItemID, short pSlot = 1)
        {
            Packet packet = new Packet(0x0025);
            packet.WriteBool(true); // Unstuck

            packet.WriteByte(1); // Items
            packet.WriteByte(0); // Mode

            {
                packet.WriteByte(0); // New item
                packet.WriteByte((byte)(pItemID / 1000000)); // Inventory
                packet.WriteShort(pSlot);

                byte type = 2;
                if (pItemID / 1000000 == 1) type = 1;
                else if (pItemID / 10000 == 500) type = 3; // Pet

                if (type == 1)
                {
                    ItemEquip equip = new ItemEquip(pItemID); // Wizet Invincible Hat 2
                    equip.Write(packet);
                }
                else if (type == 2)
                {
                    ItemRechargable regitem = new ItemRechargable(pItemID);
                    regitem.Write(packet);
                }
                else if (type == 2)
                {
                    ItemPet pet = new ItemPet(pItemID);
                    pet.Write(packet);
                }
            }


            pSession.SendPacket(packet);
        }

        public static void SendTestItem(Session pSession, ItemBase pItem, short pSlot = 1)
        {
            Packet packet = new Packet(0x0025);
            packet.WriteBool(true); // Unstuck

            packet.WriteByte(1); // Items
            packet.WriteByte(0); // Mode

            {
                packet.WriteByte(0); // New item
                packet.WriteByte((byte)(pItem.ItemID / 1000000)); // Inventory
                packet.WriteShort(pSlot); // Slot 1

                pItem.Write(packet);
            }


            pSession.SendPacket(packet);
        }


        public static bool IsExtendedSPJob(int pJobID)
        {
            int job = pJobID / 100;
            int adventurer = pJobID % 1000 / 100;
            if (pJobID / 100 == 27)
                adventurer = 2;
            if (pJobID / 100 == 36)
                adventurer = 4;

            return (
                pJobID / 1000 == 3 ||
#if !LOCALE_EMS
 pJobID / 1000 == 1 && (adventurer == 5 || adventurer == 3 || adventurer == 1 || adventurer == 2) || // Revamped KoC
#endif
                job == 22 || pJobID == 2001 || // Evan
                job == 23 || pJobID == 2002 || // Mercedes
                job == 24 || pJobID == 2003 || // Phantom
                job == 27 || pJobID == 2004 || // Luminous
                job == 51 || pJobID == 5000 || // Mihile
                job == 61 || pJobID == 6000 || // Kaiser
                job == 65 || pJobID == 6001 || // Angelic Buster
                pJobID / 10 == 57 || pJobID == 508 || // Jett
                pJobID / 1000 == 4 // JMS specials: Kanna and Hayato
                );
        }

    }
}
