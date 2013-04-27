using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public class Guild
    {
        public class GuildSkill
        {
            public int SkillID { get; set; }
            public short Level { get; set; }
            public long BoughtAt { get; set; }
            public string BoughtBy { get; set; }
            public string Unknown { get; set; }
        }

        public class GuildMember
        {
            public int CharacterID { get; set; }
            public byte Rank { get; set; }
            public int Contribution { get; set; }
        }

        public int ID { get; private set; }
        public int Points { get; private set; }
        public int AllianceID { get; private set; }
        public byte Capacity { get; private set; }
        public string Name { get; private set; }
        public string Notice { get; private set; }
        public string[] Ranks { get; private set; }

        public byte Players { get; private set; }

        public short Background { get; private set; }
        public byte BackgroundColor { get; private set; }
        public short Foreground { get; private set; }
        public byte ForegroundColor { get; private set; }

        public List<GuildSkill> Skills { get; private set; }

        public List<GuildMember> Members { get; private set; }

        public Guild()
        {
            Ranks = new string[5];
        }

        public void Decode(MaplePacket pPacket)
        {
            ID = pPacket.ReadInt();
            Name = pPacket.ReadString();

            for (byte i = 0; i < 5; i++)
                Ranks[i] = pPacket.ReadString();

            Players = pPacket.ReadByte();

            Members = new List<GuildMember>();
            for (byte i = 0; i < Players; i++)
            {
                var id = pPacket.ReadInt(); // Player ID

                Members.Add(new GuildMember() { CharacterID = id });
            }

            for (byte i = 0; i < Players; i++)
            {
                GuildMember tmp = Members[i];

                pPacket.ReadString(13); // Name
                pPacket.ReadInt(); // Job ID
                pPacket.ReadInt(); // Level OR -1
                tmp.Rank = (byte)pPacket.ReadInt(); // Guild Rank
                pPacket.ReadInt(); // Online
                pPacket.ReadInt(); // Alliance Rank
                tmp.Contribution = pPacket.ReadInt(); // Contribution
            }

            Capacity = (byte)pPacket.ReadInt();
            Background = pPacket.ReadShort();
            BackgroundColor = pPacket.ReadByte();
            Foreground = pPacket.ReadShort();
            ForegroundColor = pPacket.ReadByte();

            Notice = pPacket.ReadString();

            Points = pPacket.ReadInt();
            pPacket.ReadInt(); // Today's points
            AllianceID = pPacket.ReadInt();

            pPacket.ReadByte(); // Unk
            pPacket.ReadShort(); // Herp

            Skills = new List<GuildSkill>();
            var skills = pPacket.ReadShort(); // Buffs
            for (var i = 0; i < skills; i++)
            {
                GuildSkill gb = new GuildSkill()
                {
                    SkillID = pPacket.ReadInt(),
                    Level = pPacket.ReadShort(),
                    BoughtAt = pPacket.ReadLong(),
                    BoughtBy = pPacket.ReadString(),
                    Unknown = pPacket.ReadString()
                };

                Skills.Add(gb);
            }
        }

        public void Save(byte pWorldID)
        {
            using (InsertQueryBuilder guildTable = new InsertQueryBuilder("guilds"))
            {
                guildTable.OnDuplicateUpdate = true;
                guildTable.AddColumn("id", false);
                guildTable.AddColumn("world_id", true);
                guildTable.AddColumn("name", true);
                guildTable.AddColumn("notice", true);
                guildTable.AddColumn("rank1", true);
                guildTable.AddColumn("rank2", true);
                guildTable.AddColumn("rank3", true);
                guildTable.AddColumn("rank4", true);
                guildTable.AddColumn("rank5", true);
                guildTable.AddColumn("capacity", true);
                guildTable.AddColumn("emblem_bg", true);
                guildTable.AddColumn("emblem_bg_color", true);
                guildTable.AddColumn("emblem_fg", true);
                guildTable.AddColumn("emblem_fg_color", true);
                guildTable.AddColumn("points", true);
                guildTable.AddColumn("alliance_id", true);

                guildTable.AddRow(ID, pWorldID, Name,
                    Notice,
                    Ranks[0],
                    Ranks[1],
                    Ranks[2],
                    Ranks[3] == "" ? null : Ranks[3],
                    Ranks[4] == "" ? null : Ranks[4],
                    Capacity,
                    Background,
                    BackgroundColor,
                    Foreground,
                    ForegroundColor,
                    Points,
                    AllianceID
                    );

                guildTable.RunQuery();
            }

            // Delete members first

            MySQL_Connection.Instance.RunQuery("DELETE FROM guild_members WHERE guild_id = " + ID);

            using (InsertQueryBuilder guildMembersTable = new InsertQueryBuilder("guild_members"))
            {
                guildMembersTable.OnDuplicateUpdate = true;
                guildMembersTable.AddColumn("guild_id", true);
                guildMembersTable.AddColumn("character_id", false); // Switching guild huh?
                guildMembersTable.AddColumn("rank", true);
                guildMembersTable.AddColumn("contribution", true);

                foreach (var member in Members)
                {
                    guildMembersTable.AddRow(
                        ID,
                        member.CharacterID,
                        member.Rank,
                        member.Contribution
                        );
                }

                if (guildMembersTable.RowCount > 0)
                    MySQL_Connection.Instance.RunQuery(guildMembersTable.ToString());
            }

            using (InsertQueryBuilder guildSkillsTable = new InsertQueryBuilder("guild_skills"))
            {
                guildSkillsTable.OnDuplicateUpdate = true;
                guildSkillsTable.AddColumn("guild_id", false);
                guildSkillsTable.AddColumn("skill_id", false);
                guildSkillsTable.AddColumn("level", true);
                guildSkillsTable.AddColumn("bought_by", true);
                guildSkillsTable.AddColumn("bought_at", true);
                guildSkillsTable.AddColumn("unk", true);

                foreach (var skill in Skills)
                {
                    guildSkillsTable.AddRow(
                        ID,
                        skill.SkillID,
                        skill.Level,
                        skill.BoughtBy,
                        skill.BoughtAt,
                        skill.Unknown
                        );
                }

                if (guildSkillsTable.RowCount > 0)
                    MySQL_Connection.Instance.RunQuery(guildSkillsTable.ToString());
            }
        }
    }
}