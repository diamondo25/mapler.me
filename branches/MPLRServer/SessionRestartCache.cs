using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using MDR = MySql.Data.MySqlClient.MySqlDataReader;

namespace MPLRServer
{
    class RestartInfo
    {
        public string IP { get; set; }
        public byte[] MachineID { get; set; }
        public int CharacterID { get; set; }
        public byte WorldID { get; set; }

        public bool CompareMachineID(byte[] pInput)
        {
            for (int i = 0; i < 16; i++)
                if (pInput[i] != MachineID[i]) return false;

            return true;
        }
    }

    class SessionRestartCache
    {

        public static SessionRestartCache Instance { get; private set; }

        public static void Start()
        {
            Instance = new SessionRestartCache();

        }


        public List<RestartInfo> Cache { get; private set; }

        public SessionRestartCache()
        {
            Cache = new List<RestartInfo>();
            RestoreCache();
        }

        private void RestoreCache()
        {
            using (var q = MySQL_Connection.Instance.RunQuery("SELECT * FROM session_restarts ORDER BY ip ASC") as MDR)
            {
                while (q.Read())
                {
                    var ip = q.GetString("ip");
                    var characterid = q.GetInt32("character_id");
                    var world = q.GetByte("world_id");
                    byte[] machine_id = (byte[])q["machine_id"];
                    for (int i = 0; i < 16; i++)
                    {
                        machine_id[i] ^= (byte)(characterid * world);
                    }

                    Cache.Add(new RestartInfo
                    {
                        CharacterID = characterid,
                        IP = ip,
                        WorldID = world,
                        MachineID = machine_id
                    });
                }
            }

            Logger.WriteLine("{0} players can reconnect. They need to change channel though.", Cache.Count);
        }

        public void StoreInfo(string pIP, byte[] pMachineID, int pCharacterID, byte pWorldID)
        {
            using (InsertQueryBuilder iqb = new InsertQueryBuilder("session_restarts"))
            {
                byte[] encoded_machineid = new byte[16];

                for (int i = 0; i < 16; i++)
                {
                    encoded_machineid[i] = (byte)(pMachineID[i] ^ (byte)(pCharacterID * pWorldID));
                }

                iqb.AddColumn("machine_id");
                iqb.AddColumn("ip", true);
                iqb.AddColumns(true, "character_id", "world_id");

                iqb.OnDuplicateUpdate = true;

                iqb.AddRow(encoded_machineid, pIP, pCharacterID, pWorldID);

                iqb.RunQuery();
            }

            Cache.Add(new RestartInfo
            {
                CharacterID = pCharacterID,
                IP = pIP,
                WorldID = pWorldID,
                MachineID = pMachineID
            });
        }

        public void RemoveInfo(RestartInfo pInfo)
        {
            if (pInfo == null) return;
            using (DeleteQueryBuilder dqb = new DeleteQueryBuilder("session_restarts"))
            {
                dqb.SetWhereColumn("character_id", pInfo.CharacterID);
                dqb.SetWhereColumn("world_id", pInfo.WorldID);
                dqb.SetWhereColumn("ip", pInfo.IP);
                dqb.SetWhereColumn("machine_id", pInfo.MachineID);

                dqb.RunQuery();
            }

            Cache.Remove(pInfo);
        }

        public RestartInfo GetInfoForConnection(ClientConnection pConnection)
        {
            return Cache.Find((i) => { return (i.CharacterID == pConnection.CharacterID && i.CompareMachineID(pConnection.MachineID) && i.IP == pConnection.IP); });
        }

        public void TryRestartSession(ClientConnection pConnection, int pCharacterID, byte[] pMachineID)
        {
            RestartInfo info = Cache.Find((i) => { return (i.CharacterID == pCharacterID && i.CompareMachineID(pMachineID) && i.IP == pConnection.IP); });
            if (info != null)
            {
                // Found character
                // NOTE:
                // When a player/IP has 2 characters with the same ID on 2 different worlds
                // And the connection resets. And the player changes to the other character.
                // And a blue moon. Then worlds collide and apocalypse

                if (Internal_Storage.Store.Instance.KnownCharlist.ContainsKey(info.CharacterID) &&
                    Internal_Storage.Store.Instance.KnownCharlist[info.CharacterID].ContainsKey(info.WorldID))
                {
                    // Okay. We got this
                    var is_info = Internal_Storage.Store.Instance.KnownCharlist[info.CharacterID][info.WorldID];

                    pConnection.AccountID = is_info.AccountID;
                    pConnection.WorldID = is_info.WorldID;
                    pConnection.UserID = is_info.UserID;
                    // Do not set the IDs of the character
                    // pConnection.CharacterInternalID = info.InternalID;

                    Logger.WriteLine("Restored session for characterid {0} world {1} (IP: {2})", pCharacterID, is_info.WorldID, pConnection.IP);

                    // Scratch him off the cache list
                    Cache.Remove(info);
                }
                else
                {
                    Logger.WriteLine("Unable to restore session for {0} (IP: {1})! Not found in Internal Storage.", pCharacterID, pConnection.IP);
                }
            }
            else
            {
                Logger.WriteLine("No info found for character id {0} (IP: {1})", pCharacterID, pConnection.IP);

            }
        }

    }
}
