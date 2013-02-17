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

        public static Timeline Instance { get; private set; }
        public static void Init()
        {
            Instance = new Timeline();
            Logger.WriteLine("Initialized Timeline");
        }

        public void PushLevelUP(int pCharInternalID, byte pNewLevel)
        {
            Push(Types.LevelUP, pCharInternalID, pNewLevel.ToString());
        }

        public void PushJobUP(int pCharInternalID, ushort pJob)
        {
            Push(Types.JobUP, pCharInternalID, pJob.ToString());
        }

        public void PushSkillUP(int pCharInternalID, int pSkill, int pLevel)
        {
            Push(Types.JobUP, pCharInternalID, string.Format("{0};{1}", pSkill, pLevel));
        }

        public void PushGotFame(int pCharInternalID, bool pAdd)
        {
            Push(Types.GotFame, pCharInternalID, pAdd ? "1" : "0");
        }


        public void Push(Types pType, int pObjectID, string pData)
        {
            string type = pType.ToString();
            type = type.Substring(type.LastIndexOf('.') + 1).ToLower();

            string query = string.Format("INSERT INTO timeline VALUES (NULL, {0}, NOW(), {1}, '{2}')", (byte)pType, pObjectID, MySql.Data.MySqlClient.MySqlHelper.EscapeString(pData));
            MySQL_Connection.Instance.RunQuery(query);
        }

    }
}
