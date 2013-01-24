using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class Guild
    {
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

            for (byte i = 0; i < Players; i++)
            {
                pPacket.ReadString(13); // Name
                pPacket.ReadInt(); // Job ID
                pPacket.ReadInt(); // Level OR -1
                pPacket.ReadInt(); // Guild Rank
                pPacket.ReadInt(); // Online
                pPacket.ReadInt();
                pPacket.ReadInt(); // Alliance Rank
            }

            Capacity = (byte)pPacket.ReadInt();
            Background = pPacket.ReadShort();
            BackgroundColor = pPacket.ReadByte();
            Foreground = pPacket.ReadShort();
            ForegroundColor = pPacket.ReadByte();

            Notice = pPacket.ReadString();

            Points = pPacket.ReadInt();
            AllianceID = pPacket.ReadInt();
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

                MySQL_Connection.Instance.RunQuery(guildTable.ToString());
            }
        }
    }
}
