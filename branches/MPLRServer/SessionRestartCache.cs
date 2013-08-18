using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using MDR = MySql.Data.MySqlClient.MySqlDataReader;

namespace MPLRServer
{
   public class RestartInfo
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

   public class SessionRestartCache
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
            RestartInfo restartInfo = Cache.Find((i) => { return (i.CharacterID == pCharacterID && i.CompareMachineID(pMachineID) && i.IP == pConnection.IP); });
            if (restartInfo != null)
            {
                // Found character
                // NOTE:
                // When a player/IP has 2 characters with the same ID on 2 different worlds
                // And the connection resets. And the player changes to the other character.
                // And a blue moon. Then worlds collide and apocalypse

                var info = AccountDataCache.Instance.GetCharInfoByIDAndWorldID(pCharacterID, restartInfo.WorldID);

                if (info != null)
                {
                    if (info.AccountID != pConnection.AccountID)
                    {
                        pConnection.Logger_WriteLine("Unable to restore session for {0} (IP: {1})! Account id not the same! (Trying to hack it?)", pCharacterID, pConnection.IP);
                        return;
                    }
                    // Okay. We got this
                    pConnection.WorldID = info.WorldID;
                    pConnection.UserID = info.UserID;
                    // Do not set the IDs of the character
                    // pConnection.CharacterInternalID = info.InternalID;

                    pConnection.Logger_WriteLine("Restored session for characterid {0} world {1} (IP: {2})", pCharacterID, info.WorldID, pConnection.IP);

                    // Scratch him off the cache list
                    Cache.Remove(restartInfo);
                }
                else
                {
                    pConnection.Logger_WriteLine("Unable to restore session for {0} (IP: {1})! Not found in Internal Storage.", pCharacterID, pConnection.IP);
                }
            }
            else
            {
                pConnection.Logger_WriteLine("No info found for character id {0} (IP: {1})", pCharacterID, pConnection.IP);

            }
        }

    }
}
