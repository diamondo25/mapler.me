using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class Queries
    {
        public static void AddOrUpdateCharacter(int pID, string pName, int pUserID, byte pWorldID, byte pLevel,
            short pJob, short pStr, short pDex, short pInt, short pLuk,
            int pHP, int pMaxHP, int pMP, int pMaxMP, short pAP, short pSP,
            int pEXP, int pFame, int pMap, byte pMapPos,
            int pHonourLevel, int pHonourEXP, int pMesos, int pDemonMark,
            byte pGender, byte pSkin,
            int pEyes, int pHair
            )
        {
            if (Internal_Storage.Store.Instance.KnownCharlist.ContainsKey(pID) &&
                Internal_Storage.Store.Instance.KnownCharlist[pID].ContainsKey(pWorldID))
            {
                int internal_id = Internal_Storage.Store.Instance.KnownCharlist[pID][pWorldID].InternalID;
                using (UpdateQueryBuilder query = new UpdateQueryBuilder("characters"))
                {
                    query.SetColumn("name", pName);
                    query.SetColumn("userid", pUserID);
                    query.SetColumn("world_id", pWorldID);
                    query.SetColumn("level", pLevel);
                    query.SetColumn("job", pJob);
                    query.SetColumn("str", pStr);
                    query.SetColumn("dex", pDex);
                    query.SetColumn("int", pInt);
                    query.SetColumn("luk", pLuk);
                    query.SetColumn("chp", pHP);
                    query.SetColumn("mhp", pMaxHP);
                    query.SetColumn("cmp", pMP);
                    query.SetColumn("mmp", pMaxMP);
                    query.SetColumn("ap", pAP);
                    query.SetColumn("sp", pSP);
                    query.SetColumn("exp", pEXP);
                    query.SetColumn("fame", pFame);
                    query.SetColumn("map", pMap);
                    query.SetColumn("pos", pMapPos);
                    query.SetColumn("gender", pGender);
                    query.SetColumn("skin", pSkin);
                    query.SetColumn("eyes", pEyes);
                    query.SetColumn("hair", pHair);
                    query.SetColumn("last_update", new MySQL_Connection.NowType());

                    query.SetColumn("honourlevel", pHonourLevel);
                    query.SetColumn("honourexp", pHonourEXP);
                    query.SetColumn("mesos", pMesos);
                    query.SetColumn("demonmark", pDemonMark);

                    query.SetWhereColumn("internal_id", internal_id);

                    string q = query.ToString();
                    System.IO.File.WriteAllText("sql_addorupdate.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);

                    if (result >= 1)
                    {
                        Logger.WriteLine("Updated character info: {0}", internal_id);
                    }
                    else
                    {
                        Logger.WriteLine("Failed to update character info");
                    }
                }
            }
            else
            {
                using (InsertQueryBuilder query = new InsertQueryBuilder("characters"))
                {
                    query.OnDuplicateUpdate = true;
                    query.AddColumn("internal_id");
                    query.AddColumn("id");
                    query.AddColumn("name");
                    query.AddColumns(true, new string[] { "userid", "world_id", "level", "job", "str", "dex", "int", "luk", "chp", "mhp", "cmp", "mmp", "ap", "sp", "exp", "fame", "map", "pos", "gender", "skin", "eyes", "hair" });
                    query.AddColumns(true, new string[] { "honourlevel", "honourexp", "mesos", "demonmark" });
                    query.AddColumn("last_update", true);


                    query.AddRow(null, pID, pName, pUserID, pWorldID, pLevel,
                        pJob, pStr, pDex, pInt, pLuk,
                        pHP, pMaxHP, pMP, pMaxMP, pAP, pSP,
                        pEXP, pFame, pMap, pMapPos, pGender, pSkin,
                        pHonourLevel, pHonourEXP, pMesos, pDemonMark,
                        pEyes, pHair, new MySQL_Connection.NowType());

                    string q = query.ToString();

                    System.IO.File.WriteAllText("sql_addorupdate.sql", q);

                    int result = (int)MySQL_Connection.Instance.RunQuery(q);

                    if (result >= 1)
                    {
                        Logger.WriteLine("Inserted character info: {0}", MySQL_Connection.Instance.GetLastInsertId());
                    }
                    else
                    {
                        Logger.WriteLine("Failed to insert character info");
                    }
                }
            }
        }
    }
}
