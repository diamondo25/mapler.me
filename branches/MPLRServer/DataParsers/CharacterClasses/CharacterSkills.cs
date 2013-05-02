using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class CharacterSkills
    {
        public class Skill
        {
            public int ID { get; set; }
            public int Level { get; set; }
            public int MasterLevel { get; set; }
            public long Expiration { get; set; }
        }

        public Dictionary<int, Skill> SkillList { get; private set; }
        public Dictionary<int, int> Cooldowns { get; private set; }

        public CharacterSkills()
        {
            SkillList = new Dictionary<int, Skill>();
            Cooldowns = new Dictionary<int, int>();
        }

        public void Decode(ClientConnection pConnection, MaplePacket pPacket)
        {
            if (pPacket.ReadBool())
            {
                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    Skill skill = new Skill();
                    skill.ID = pPacket.ReadInt();
                    skill.Level = pPacket.ReadInt();
                    skill.Expiration = pPacket.ReadLong();

                    if (GameHelper.is_skill_need_master_level(skill.ID))
                        skill.MasterLevel = pPacket.ReadInt();
                    else
                        skill.MasterLevel = -1;

                    if (SkillList.ContainsKey(skill.ID)) // NEXON
                        pConnection.Logger_WriteLine("Found duplicate skill {0}", skill.ID);
                    else
                        SkillList.Add(skill.ID, skill);
                }

                for (int i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadInt();
                    pPacket.ReadShort();
                }
            }
            else
            {
                pConnection.Logger_WriteLine("Character has 'new' skilllist!");
                // 0.0
                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadInt();
                    pPacket.ReadInt();
                }

                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadInt();
                }

                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadInt();
                    pPacket.ReadLong();
                }

                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadInt();
                }

                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadInt();
                    pPacket.ReadInt();
                }

                for (short i = pPacket.ReadShort(); i > 0; i--)
                {
                    pPacket.ReadInt();
                }

            }

            short amnt = pPacket.ReadShort();

            for (short i = 0; i < amnt; i++)
            {
                Cooldowns.Add(pPacket.ReadInt(), pPacket.ReadInt());
            }
        }

        
    }
}
