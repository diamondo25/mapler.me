using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public class CharacterCacheInfo
    {
        public int ID { get; private set; }
        public int InternalID { get; private set; }
        public int AccountID { get; private set; }
        public int UserID { get; private set; }
        public byte WorldID { get; private set; }
        public string Name { get; private set; }
        public Dictionary<int, Dictionary<short, int>> SlotHashes { get; private set; }

        public void Initialize(MySql.Data.MySqlClient.MySqlDataReader pReader)
        {
            UserID = pReader.GetInt32("userid");
            ID = pReader.GetInt32("id");
            InternalID = pReader.GetInt32("internal_id");
            Name = pReader.GetString("name");
            WorldID = pReader.GetByte("world_id");

            AccountID = AccountDataCache.Instance.KnownUserlist[UserID];

            SlotHashes = new Dictionary<int, Dictionary<short, int>>();
        }

        ~CharacterCacheInfo()
        {
            if (SlotHashes != null)
            {
                foreach (var kvp in SlotHashes)
                    kvp.Value.Clear();
                SlotHashes.Clear();
                SlotHashes = null;
            }

        }
    }

    public class AccountDataCache
    {

        public Dictionary<int, int> KnownUserlist { get; private set; }
        public Dictionary<int, CharacterCacheInfo> KnownCharlist { get; private set; }

        public static AccountDataCache Instance { get; private set; }
        public static void Initialize()
        {
            Instance = new AccountDataCache();
            Instance.Load();
        }

        public CharacterCacheInfo GetCharInfoByInternalID(int pInternalID)
        {
            if (KnownCharlist.ContainsKey(pInternalID))
                return KnownCharlist[pInternalID];
            return null;
        }

        public CharacterCacheInfo GetCharInfoByIDAndWorldID(int pCharacterID, byte pWorldID)
        {
            var info = KnownCharlist.Values.Where(a => { return a.ID == pCharacterID && a.WorldID == pWorldID; }).ToList();
            if (info.Count == 0)
                return null;
            return info.First();
        }

        public bool DeleteCharacterInfo(int pCharacterID, byte pWorldID, int pAccountID, out int pInternalID)
        {
            pInternalID = -1;
            var info = GetCharInfoByIDAndWorldID(pCharacterID, pWorldID);
            if (info == null) return false;

            if (info.AccountID != pAccountID) return false;

            pInternalID = info.InternalID;
            return KnownCharlist.Remove(info.InternalID);
        }


        public void DeleteItemChecksum(ClientConnection pConnection, ushort pInventory, short pSlot)
        {
            var info = GetCharInfoByIDAndWorldID(pConnection.CharacterID, pConnection.WorldID);
            if (info == null)
                throw new Exception("DeleteItemChecksum when character not found? ID: " + pConnection.CharacterID + "; WorldID: " + pConnection.WorldID);

            if (!info.SlotHashes.ContainsKey(pInventory))
                return;
            if (!info.SlotHashes[pInventory].ContainsKey(pSlot))
                return;

            info.SlotHashes[pInventory].Remove(pSlot);
        }

        public void SetChecksumOfSlot(int pCharacterID, byte pWorldID, ushort pInventory, short pSlot, int pChecksum)
        {
            var info = GetCharInfoByIDAndWorldID(pCharacterID, pWorldID);
            if (info == null)
                throw new Exception("SetChecksumOfSlot when character not found? ID: " + pCharacterID + "; WorldID: " + pWorldID);


            if (!info.SlotHashes.ContainsKey(pInventory))
                info.SlotHashes.Add(pInventory, new Dictionary<short, int>());

            if (info.SlotHashes[pInventory].ContainsKey(pSlot))
                info.SlotHashes[pInventory][pSlot] = pChecksum;
            else
                info.SlotHashes[pInventory].Add(pSlot, pChecksum);
        }

        public void Load()
        {
            KnownUserlist = new Dictionary<int, int>();
            using (var result = MySQL_Connection.Instance.RunQuery("SELECT id, account_id FROM users") as MySql.Data.MySqlClient.MySqlDataReader)
            {
                while (result.Read())
                    KnownUserlist.Add(result.GetInt32(0), result.GetInt32(1));
            }

            Logger.WriteLine("Loaded {0} users!", KnownUserlist.Count);

            KnownCharlist = new Dictionary<int, CharacterCacheInfo>();

            LoadBaseData();

            Logger.WriteLine("Loaded {0} characters!", KnownCharlist.Count);

            LoadInventoryHashes();
        }

        public void LoadBaseData(string pName = null)
        {
            using (var result = MySQL_Connection.Instance.RunQuery("SELECT id, internal_id, userid, world_id, name FROM characters" + (pName != null ? " WHERE name = '" + MySql.Data.MySqlClient.MySqlHelper.EscapeString(pName) + "'" : "")) as MySql.Data.MySqlClient.MySqlDataReader)
            {
                while (result.Read())
                {
                    CharacterCacheInfo ch = new CharacterCacheInfo();
                    ch.Initialize(result);

                    KnownCharlist.Add(ch.InternalID, ch);
                }
            }

        }

        public void LoadInventoryHashes(int? pInternalID = null, bool reload = false)
        {
            if (pInternalID != null && reload)
            {
                var character = GetCharInfoByInternalID(pInternalID.Value);
                if (character != null)
                    character.SlotHashes.Clear();
            }

            using (var result = MySQL_Connection.Instance.RunQuery("SELECT character_id, inventory, slot, `checksum` FROM items" + (pInternalID.HasValue ? " WHERE character_id = " + pInternalID.Value : "") + " ORDER BY character_id") as MySql.Data.MySqlClient.MySqlDataReader)
            {
                CharacterCacheInfo ch = null;
                int lastid = 0;
                while (result.Read())
                {
                    if (lastid != result.GetInt32("character_id"))
                    {
                        lastid = result.GetInt32("character_id");
                        ch = GetCharInfoByInternalID(lastid);
                        if (ch == null)
                        {
                            lastid = 0;
                            continue;
                        }
                    }

                    ushort inventory = result.GetUInt16("inventory");
                    if (!ch.SlotHashes.ContainsKey(inventory))
                        ch.SlotHashes.Add(inventory, new Dictionary<short, int>());

                    ch.SlotHashes[inventory].Add(result.GetInt16("slot"), result.GetInt32("checksum"));
                }
            }
        }
    }
}
