using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class CommandHandler
    {
        public Dictionary<string, Action<ClientConnection, string[]>> CommandHandlers { get; private set; }

        public static CommandHandler Instance { get; private set; }

        public static void Initialize()
        {
            Instance = new CommandHandler()
            {
                CommandHandlers = new Dictionary<string,Action<ClientConnection, string[]>>()
            };

            Instance.CommandHandlers.Add("report", (pConnection, pArguments) =>
            {
                if (pArguments.Length == 1)
                {
                    string name = pArguments[0];
                    if (name.Length > 12) return;

                    bool found = false;
                    string query = "SELECT name FROM character_views WHERE name LIKE '" + MySql.Data.MySqlClient.MySqlHelper.EscapeString(name) + "%' AND mapid = " + pConnection.CharData.Stats.MapID;
                    using (var result = MySQL_Connection.Instance.RunQuery(query) as MySql.Data.MySqlClient.MySqlDataReader)
                    {
                        if (result.HasRows && result.Read())
                        {
                            name = result.GetString(0);
                            found = true;
                        }
                        else
                        {
                            Logger.WriteLine("Could not find {0} for {1}", name, pConnection.CharData.Stats.Name);
                        }
                    }

                    if (found)
                    {
                        using (InsertQueryBuilder iqb = new InsertQueryBuilder("reports"))
                        {
                            iqb.AddColumns(false, "id", "name", "reported_by", "reported_when", "mapid");
                            iqb.AddRow(null, name, pConnection.CharacterInternalID, new MySQL_Connection.NowType(), pConnection.CharData.Stats.MapID);

                            int result = (int)MySQL_Connection.Instance.RunQuery(iqb.ToString());
                            if (result != 0)
                            {
                                Logger.WriteLine("Reported {0} (by {1}). Requesting Screenshot...", name, pConnection.CharData.Stats.Name);
                                pConnection.LastReportID = MySQL_Connection.Instance.GetLastInsertId();
                                using (MaplePacket pack = new MaplePacket(MaplePacket.CommunicationType.Internal, (ushort)0xFFFE))
                                {
                                    pack.SwitchOver();
                                    pConnection.SendPacket(pack);
                                }
                            }
                            else
                            {
                                Logger.WriteLine("Report FAIL {0} (by {1})", name, pConnection.CharData.Stats.Name);
                            }
                        }

                    }
                }
            });
        }


        public void HandleCommand(ClientConnection pConnection, string pCommand, string[] pArguments)
        {
            if (CommandHandlers.ContainsKey(pCommand))
            {
                CommandHandlers[pCommand](pConnection, pArguments);
            }
        }
    }
}
