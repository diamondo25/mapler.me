using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public class Alliance
    {
        public int ID { get; private set; }
        public string Name { get; private set; }
        public string[] Ranks { get; private set; }

        public int[] GuildIDs { get; private set; }
        public int Capacity { get; private set; }
        public string Notice { get; private set; }

        public void Decode(MaplePacket pPacket)
        {
            this.ID = pPacket.ReadInt();
            this.Name = pPacket.ReadString();
            this.Ranks = new string[5];
            for (int i = 0; i < 5; i++)
                this.Ranks[i] = pPacket.ReadString();

            byte guilds = pPacket.ReadByte();
            this.GuildIDs = new int[guilds];
            for (int i = 0; i < guilds; i++)
                this.GuildIDs[i] = pPacket.ReadInt();

            this.Capacity = pPacket.ReadInt();
            this.Notice = pPacket.ReadString();
        }

        public static void DecodeGuilds(MaplePacket pPacket, byte pWorldID)
        {
            int guilds = pPacket.ReadInt();
            for (int i = 0; i < guilds; i++)
            {
                Guild guild = new Guild();
                guild.Decode(pPacket);
                guild.Save(pWorldID);
            }
        }
            
        public void Save(byte pWorldID)
        {
            using (InsertQueryBuilder allianceTable = new InsertQueryBuilder("alliances"))
            {
                allianceTable.OnDuplicateUpdate = true;
                allianceTable.AddColumn("id", false);
                allianceTable.AddColumn("world_id", true);
                allianceTable.AddColumn("name", true);
                allianceTable.AddColumn("notice", true);
                allianceTable.AddColumn("rank1", true);
                allianceTable.AddColumn("rank2", true);
                allianceTable.AddColumn("rank3", true);
                allianceTable.AddColumn("rank4", true);
                allianceTable.AddColumn("rank5", true);
                allianceTable.AddColumn("capacity", true);

                allianceTable.AddRow(ID, pWorldID, Name,
                    Notice,
                    Ranks[0],
                    Ranks[1],
                    Ranks[2],
                    Ranks[3] == "" ? null : Ranks[3],
                    Ranks[4] == "" ? null : Ranks[4],
                    Capacity
                    );

                allianceTable.RunQuery();
            }
        }
    }
}
