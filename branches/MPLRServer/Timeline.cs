using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public class Timeline
    {
        public enum Types
        {
            LevelUP = 1, // MySQL counts from 1
            JobUP, 
            SkillUP,
            GotEXP, // Err...
            GotFame,
        }

        private static Dictionary<Types, string> _type_name_map = new Dictionary<Types, string>();

        public static Timeline Instance { get; private set; }
        public static void Init()
        {
            _type_name_map.Add(Types.LevelUP, "levelup");
            _type_name_map.Add(Types.JobUP, "jobup");
            _type_name_map.Add(Types.SkillUP, "skillup");
            _type_name_map.Add(Types.GotEXP, "gotexp");
            _type_name_map.Add(Types.GotFame, "gotfame");

            Instance = new Timeline();
            Logger.WriteLine("Initialized Timeline");
        }

        public void PushLevelUP(int pAccountID, int pCharInternalID, byte pNewLevel)
        {
            Push(Types.LevelUP, pAccountID, pCharInternalID, pNewLevel.ToString());
        }

        public void PushJobUP(int pAccountID, int pCharInternalID, ushort pJob)
        {
            Push(Types.JobUP, pAccountID, pCharInternalID, pJob.ToString());
        }

        public void PushSkillUP(int pAccountID, int pCharInternalID, int pSkill, int pLevel)
        {
            Push(Types.SkillUP, pAccountID, pCharInternalID, string.Format("{0};{1}", pSkill, pLevel));
        }

        public void PushGotFame(int pAccountID, int pCharInternalID, bool pAdd, int pNewFame)
        {
            Push(Types.GotFame, pAccountID, pCharInternalID, (pAdd ? "1" : "0") + ";" + pNewFame);
        }


        public void Push(Types pType, int pAccountID, int pCharInternalID, string pData)
        {
            MySQL_Connection.Instance.RunQuery(string.Format("INSERT INTO timeline VALUES (NULL, '{0}', NOW(), {1}, {2}, '{3}')", _type_name_map[pType], pAccountID, pCharInternalID, MySql.Data.MySqlClient.MySqlHelper.EscapeString(pData)));
        }

    }
}
