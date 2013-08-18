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
            EXPPoint, // Err...
            GotFame,
        }

        private static Dictionary<Types, string> _type_name_map = new Dictionary<Types, string>();

        public static Timeline Instance { get; private set; }
        public static void Init()
        {
            _type_name_map.Add(Types.LevelUP, "levelup");
            _type_name_map.Add(Types.JobUP, "jobup");
            _type_name_map.Add(Types.SkillUP, "skillup");
            _type_name_map.Add(Types.EXPPoint, "exppoint");
            _type_name_map.Add(Types.GotFame, "gotfame");

            Instance = new Timeline();
            Logger.WriteLine("Initialized Timeline");
        }

        public void PushLevelUP(ClientConnection pConnection, byte pNewLevel)
        {
            Push(Types.LevelUP, pConnection.AccountID, pConnection.CharacterInternalID, pNewLevel.ToString());
        }

        public void PushJobUP(ClientConnection pConnection, ushort pJob)
        {
            Push(Types.JobUP, pConnection.AccountID, pConnection.CharacterInternalID, pJob.ToString());
        }

        public void PushSkillUP(ClientConnection pConnection, int pSkill, int pLevel)
        {
            Push(Types.SkillUP, pConnection.AccountID, pConnection.CharacterInternalID, string.Format("{0};{1}", pSkill, pLevel));
        }

        public void PushGotFame(ClientConnection pConnection, int pAdd, int pNewFame)
        {
            Push(Types.GotFame, pConnection.AccountID, pConnection.CharacterInternalID, pAdd + ";" + pNewFame);
        }

        public void PushExpPoint(ClientConnection pConnection, int pVal)
        {
            Push(Types.EXPPoint, pConnection.AccountID, pConnection.CharacterInternalID, pVal.ToString() + ";" + pConnection.CharData.Stats.MapID);
        }


        public void Push(Types pType, int pAccountID, int pCharInternalID, string pData)
        {
            MySQL_Connection.Instance.RunQuery(string.Format("INSERT INTO timeline VALUES (NULL, '{0}', NOW(), {1}, {2}, '{3}')", _type_name_map[pType], pAccountID, pCharInternalID, MySql.Data.MySqlClient.MySqlHelper.EscapeString(pData)));
        }

    }
}
