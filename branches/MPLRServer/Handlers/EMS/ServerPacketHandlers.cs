using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
#if LOCALE_EMS
    public class ServerPacketHandlers : IServerPacketHandlers
    {
        private static ServerPacketHandlers _instance;
        public static ServerPacketHandlers Instance { get { return _instance = _instance ?? new ServerPacketHandlers(); } }

        public override void HandleLogin(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte error = pPacket.ReadByte();
            if (error != 0)
            {
                pConnection.Logger_WriteLine("Got Status: {0}", error);
                if (error == 0x07)
                {
                    pConnection.Logger_WriteLine("Already logged in?");
                }
                return;
            }

            int userid = pPacket.ReadInt();
            pPacket.ReadByte(); // Gender or GenderSelect/PinSelect
            pPacket.ReadByte();
            pPacket.ReadByte(); // Admin? Has bitflag 5
            string username = pPacket.ReadString(); // Username
            pPacket.ReadByte();
            pPacket.ReadByte();
            DateTime creationtime = DateTime.FromFileTime(pPacket.ReadLong()); // Incorrect :/. Nowhere to be found

            if (pPacket.ReadBool() == false)
                pPacket.ReadString(); // Username, with astriks as protection: 'd**mondo2*'

            pPacket.ReadString(); // wat.
            if (pPacket.ReadBool())
                pPacket.ReadBytes(15); // Every job that can be chosen

            pPacket.ReadInt(); // YYYYMMDDHH, as in character info -.-?


            ParseLogin(pConnection, userid, username, creationtime);
        }


        public override void HandleStatUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            pPacket.ReadByte();
            long updateFlag = pPacket.ReadLong();
            if (updateFlag == 0) return; // Fake Update -.- / Unstuck

            bool didsomething = false;

            if (CheckFlag(updateFlag, 1)) // Skin
            {
                didsomething = true;
                pConnection.CharData.Stats.Skin = pPacket.ReadByte();
            }
            if (CheckFlag(updateFlag, 2)) // Eyes
            {
                didsomething = true;
                pConnection.CharData.Stats.Face = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 4)) // Eyes
            {
                didsomething = true;
                pConnection.CharData.Stats.Hair = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x10))
            {
                didsomething = true;
                var level = pPacket.ReadByte();
                Timeline.Instance.PushLevelUP(pConnection, level);
                pConnection.CharData.Stats.Level = level;
                pConnection.Logger_WriteLine("{0} leveled up to level {1}!!!", pConnection.CharData.Stats.Name, level);
            }
            if (CheckFlag(updateFlag, 0x20))
            {
                didsomething = true;
                var jobid = pPacket.ReadShort();
                Timeline.Instance.PushJobUP(pConnection, (ushort)jobid);
                pConnection.CharData.Stats.JobID = jobid;
                pConnection.Logger_WriteLine("{0} changed to job {1}!!!", pConnection.CharData.Stats.Name, jobid);
            }
            if (CheckFlag(updateFlag, 0x40))
            {
                didsomething = true;
                pConnection.CharData.Stats.Str = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x80))
            {
                didsomething = true;
                pConnection.CharData.Stats.Dex = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x100))
            {
                didsomething = true;
                pConnection.CharData.Stats.Int = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x200))
            {
                didsomething = true;
                pConnection.CharData.Stats.Luk = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x400))
            {
                didsomething = true;
                pConnection.CharData.Stats.HP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x800))
            {
                didsomething = true;
                pConnection.CharData.Stats.MaxHP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x1000))
            {
                didsomething = true;
                pConnection.CharData.Stats.MP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x2000))
            {
                didsomething = true;
                pConnection.CharData.Stats.MaxMP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x4000))
            {
                didsomething = true;
                pConnection.CharData.Stats.AP = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x8000))
            {
                didsomething = true;

                short a1 = pConnection.CharData.Stats.JobID;
                pConnection.CharData.Stats.SPData.Clear();

                if (GameHelper.IsExtendedSPJob(pConnection.CharData.Stats.JobID))
                {

                    byte amnt = pPacket.ReadByte();
                    List<byte> haslist = new List<byte>();
                    for (int j = 0; j < amnt; j++)
                    {
                        byte v1 = pPacket.ReadByte(); // Job ID
                        int v2 = pPacket.ReadInt(); // Amount
                        pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(v1, v2));

                        haslist.Add(v1);
                    }
                    for (byte j = 1; j < 20; j++)
                    {
                        if (!haslist.Contains(j))
                            pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(j, 0));
                    }

                }
                else
                {
                    pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(0, pPacket.ReadShort()));
                }
            }

            if (CheckFlag(updateFlag, 0x10000))
            {
                didsomething = true;
                long newexp = pPacket.ReadLong();
                byte point = (byte)EXPTable.GetLevelPercentage(pConnection.CharData.Stats.Level, newexp);

                if (pConnection.LastExpPoint != point)
                {
                    // Ohhh
                    Timeline.Instance.PushExpPoint(pConnection, point);
                }

                pConnection.CharData.Stats.EXP = newexp;
                pConnection.LastExpPoint = point;

            }

            if (CheckFlag(updateFlag, 0x20000))
            {
                didsomething = true;
                int fame = pPacket.ReadInt();
                Timeline.Instance.PushGotFame(pConnection, fame - pConnection.CharData.Stats.Fame, fame);
                pConnection.CharData.Stats.Fame = fame;
            }

            if (CheckFlag(updateFlag, 0x40000))
            {
                didsomething = true;
                pConnection.CharData.Stats.Mesos = pPacket.ReadLong();
            }
            // EMS Weird stuff
            if (CheckFlag(updateFlag, 0x80000))
            {
                var value = pPacket.ReadLong();
                pConnection.Logger_WriteLine("0x80000 | {0}", value);
            }
            if (CheckFlag(updateFlag, 0x100000))
            {
                var value = pPacket.ReadByte();
                pConnection.Logger_WriteLine("0x100000 | {0}", value);
            }

            if (CheckFlag(updateFlag, 0x200000))
            {
                // Ambition/Charisma D:
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Charisma] = pPacket.ReadInt();
            }

            if (CheckFlag(updateFlag, 0x400000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Insight] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x800000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Willpower] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x1000000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.CraftDiligence] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x2000000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Empathy] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x4000000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Charm] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x8000000))
            {
                pPacket.ReadBytes(21);
            }

            if (CheckFlag(updateFlag, 0x10000000))
            {
                pPacket.ReadByte();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadByte();
            }

            if (CheckFlag(updateFlag, 0x20000000))
            {
                for (byte i = 0; i < 9; i++)
                {
                    pPacket.ReadInt();
                    pPacket.ReadByte();
                    pPacket.ReadInt();
                }
            }

            if (CheckFlag(updateFlag, 0x40000000))
            {
                var value1 = pPacket.ReadInt();
                var value2 = pPacket.ReadByte();
                var value3 = pPacket.ReadInt();
                pConnection.Logger_WriteLine("0x40000000 | {0} | {1} | {2}", value1, value2, value3);
            }

            if (CheckFlag(updateFlag, 0x80000000))
            {
                var value1 = pPacket.ReadByte();
                var value2 = pPacket.ReadByte();
                pConnection.Logger_WriteLine("0x80000000 | {0} | {1}", value1, value2);
            }

            if (CheckFlag(updateFlag, 0x100000000))
            {
                var value = pPacket.ReadInt();
                pConnection.Logger_WriteLine("0x100000000 | {0}", value);
            }

            if (CheckFlag(updateFlag, 0x200000000))
            {
                var value = pPacket.ReadInt();
                pConnection.Logger_WriteLine("0x200000000 | {0}", value);
            }

            if (didsomething)
            {
                pConnection.CharData.SaveCharacterInfo(pConnection);

                pConnection.SendTimeUpdate();
            }
        }
    }
#endif
}
