using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer.Internal_Storage
{
    public class Store
    {
        public class CharInfo
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

                AccountID = Store.Instance.KnownUserlist[UserID];

                SlotHashes = new Dictionary<int, Dictionary<short, int>>();
            }
        }

        public struct InternalCharBound
        {
            public int InternalID { get; set; }
            public int ID { get; set; }
            public byte WorldID { get; set; }
        }

        public Dictionary<int, int> KnownUserlist { get; private set; }
        public Dictionary<int, Dictionary<byte, CharInfo>> KnownCharlist { get; private set; }
        public Dictionary<int, InternalCharBound> KnownCharlist_INTERNAL { get; private set; }

        public static Store Instance { get; private set; }
        public static void Initialize()
        {
            Instance = new Store();
            Instance.Load();
        }

        public CharInfo GetCharInfoByIntenalID(int pInternalID)
        {
            if (!KnownCharlist_INTERNAL.ContainsKey(pInternalID)) return null;
            var tmp = KnownCharlist_INTERNAL[pInternalID];
            if (!KnownCharlist.ContainsKey(tmp.ID) || !KnownCharlist[tmp.ID].ContainsKey(tmp.WorldID)) return null;
            return KnownCharlist[tmp.ID][tmp.WorldID];
        }

        public void SetChecksumOfSlot(int pCharacterID, byte pWorldID, byte pInventory, short pSlot, int pChecksum)
        {
            if (!Internal_Storage.Store.Instance.KnownCharlist[pCharacterID][pWorldID].SlotHashes.ContainsKey(pInventory))
                Internal_Storage.Store.Instance.KnownCharlist[pCharacterID][pWorldID].SlotHashes.Add(pInventory, new Dictionary<short, int>());

            if (Internal_Storage.Store.Instance.KnownCharlist[pCharacterID][pWorldID].SlotHashes[pInventory].ContainsKey(pSlot))
                Internal_Storage.Store.Instance.KnownCharlist[pCharacterID][pWorldID].SlotHashes[pInventory][pSlot] = pChecksum;
            else
                Internal_Storage.Store.Instance.KnownCharlist[pCharacterID][pWorldID].SlotHashes[pInventory].Add(pSlot, pChecksum);
        }

        public void Load()
        {
            KnownUserlist = new Dictionary<int, int>();
            using (var result = MySQL_Connection.Instance.RunQuery("SELECT id, account_id FROM users") as MySql.Data.MySqlClient.MySqlDataReader)
            {
                while (result.Read())
                {
                    KnownUserlist.Add(result.GetInt32(0), result.GetInt32(1));
                }
            }

            Logger.WriteLine("Loaded {0} users!", KnownUserlist.Count);

            KnownCharlist = new Dictionary<int, Dictionary<byte, CharInfo>>();
            KnownCharlist_INTERNAL = new Dictionary<int, InternalCharBound>();

            LoadBaseData();

            Logger.WriteLine("Loaded {0} characters!", KnownCharlist_INTERNAL.Count);

            LoadInventoryHashes();
        }

        public void LoadBaseData(string pName = null)
        {
            using (var result = MySQL_Connection.Instance.RunQuery("SELECT id, internal_id, userid, world_id, name FROM characters" + (pName != null ? " WHERE name = '" + MySql.Data.MySqlClient.MySqlHelper.EscapeString(pName) + "'" : "")) as MySql.Data.MySqlClient.MySqlDataReader)
            {
                while (result.Read())
                {
                    CharInfo ch = new CharInfo();
                    ch.Initialize(result);

                    if (!KnownCharlist.ContainsKey(ch.ID))
                    {
                        KnownCharlist.Add(ch.ID, new Dictionary<byte, CharInfo>());
                    }

                    if (!KnownCharlist[ch.ID].ContainsKey(ch.WorldID))
                        KnownCharlist[ch.ID].Add(ch.WorldID, ch);
                    else
                        KnownCharlist[ch.ID][ch.WorldID] = ch;

                    if (!KnownCharlist_INTERNAL.ContainsKey(ch.InternalID))
                        KnownCharlist_INTERNAL.Add(ch.InternalID, new InternalCharBound() { ID = ch.ID, InternalID = ch.InternalID, WorldID = ch.WorldID });
                    else
                        KnownCharlist_INTERNAL[ch.InternalID] = new InternalCharBound() { ID = ch.ID, InternalID = ch.InternalID, WorldID = ch.WorldID };
                }
            }

        }

        public void LoadInventoryHashes(int? pInternalID = null, bool reload = false)
        {
            if (pInternalID != null && reload)
            {
                var character = GetCharInfoByIntenalID(pInternalID.Value);
                if (character != null)
                {
                    character.SlotHashes.Clear();
                }
            }

            using (var result = MySQL_Connection.Instance.RunQuery("SELECT character_id, inventory, slot, `checksum` FROM items" + (pInternalID.HasValue ? " WHERE character_id = " + pInternalID.Value : "") + " ORDER BY character_id") as MySql.Data.MySqlClient.MySqlDataReader)
            {
                CharInfo ch = null;
                int lastid = 0;
                while (result.Read())
                {
                    if (lastid != result.GetInt32("character_id"))
                    {
                        lastid = result.GetInt32("character_id");
                        ch = GetCharInfoByIntenalID(lastid);
                        if (ch == null)
                        {
                            lastid = 0;
                            continue;
                        }
                    }

                    int inventory = result.GetInt32("inventory");
                    if (!ch.SlotHashes.ContainsKey(inventory))
                        ch.SlotHashes.Add(inventory, new Dictionary<short, int>());

                    ch.SlotHashes[inventory].Add(result.GetInt16("slot"), result.GetInt32("checksum"));
                }
            }
        }
    }
}
