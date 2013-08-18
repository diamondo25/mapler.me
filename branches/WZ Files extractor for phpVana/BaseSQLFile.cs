using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.IO;
using MySql.Data.MySqlClient;

namespace WZ_Files_extractor_for_phpVana
{
    class BaseSQLFile
    {
        protected StringBuilder _buffer;
        int amountOfRows = 0;
        public const int MAX_ROWS = 20000;
        bool wroteSomething = false;
        protected string _insert = "";

        public void AppendRowInternal(params object[] pColumns)
        {
            wroteSomething = true;
            if (amountOfRows == 0)
            {
                _buffer.Append(_insert + "\r\n");
            }

            bool lastrow = amountOfRows++ == MAX_ROWS;
            
            string row = "(";
            bool first = true;
            foreach (var column in pColumns)
            {
                if (!first) 
                    row += ", ";
                first = false;

                string value = "";
                if (column is string)
                    if (!Extractor.isNumeric((string)column))
                        value = "'" + MySqlHelper.EscapeString((string)column) + "'";
                    else 
                        value = column.ToString();
                else if (column == null)
                    value = "NULL";
                else
                   value = column.ToString();
                if (value == "")
                    row += "''";
                else
                    row += value;
            }

            row += ")";

            _buffer.Append(row.Replace(@"\\r", @"\r").Replace(@"\\n", @"\n"));

            if (!lastrow)
                _buffer.Append(",\r\n");
            else
            {
                _buffer.Append(";\r\n\r\n");
                amountOfRows = 0;
            }
        }


        public void Dump(string filename)
        {
            if (!wroteSomething) return;
            var tmp = _buffer.ToString();
            tmp = tmp.Trim();
            if (tmp.EndsWith(",")) tmp = tmp.Remove(tmp.Length - 1) + ';';

            File.WriteAllText(filename, tmp);
        }
    }


    class SQLStrings : BaseSQLFile
    {
        public SQLStrings()
        {
            this._insert = "INSERT INTO `strings` (`objecttype`, `objectid`, `key`, `value`) VALUES ";

            this._buffer = new StringBuilder(@"
DROP TABLE IF EXISTS `strings`;

CREATE TABLE `strings` (
    `objecttype` enum('map', 'item', 'quest', 'skill', 'mob', 'familiar_skill', 'item_option', 'item_spec', 'npc') NOT NULL,
    `objectid` int(11) NOT NULL,
    `key` varchar(5) NOT NULL,
    `value` text NOT NULL,
    KEY `objectid` (`objectid`),
    KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

");
        }

        public static SQLStrings Instance { get; private set; }

        public static void Init()
        {
            Instance = new SQLStrings();
        }

        public void AppendRow(string objecttype, int objectid, string key, string value)
        {
            if (value == null) return; // value may not be NULL
            base.AppendRowInternal(objecttype, objectid, key, value);
        }
    }

    class SQLData : BaseSQLFile
    {
        public SQLData()
        {
            this._insert = "INSERT INTO `phpVana_characterwz` (`itemid`, `key`, `value`) VALUES ";

            this._buffer = new StringBuilder(@"
DROP TABLE IF EXISTS `phpVana_characterwz`;

CREATE TABLE `phpVana_characterwz` (
    `itemid` int(11) NOT NULL,
    `key` varchar(45) NOT NULL,
    `value` varchar(45) NOT NULL,
    KEY `itemid` (`itemid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

");
        }

        public static SQLData Instance { get; private set; }

        public static void Init()
        {
            Instance = new SQLData();
        }

        Dictionary<int, List<string>> done = new Dictionary<int, List<string>>();

        public void AppendRow(int itemid, string key, string value)
        {
            if (value == null) return; // value may not be NULL
            if (!done.ContainsKey(itemid))
            {
                done.Add(itemid, new List<string>());
            }
            if (done[itemid].Contains(key)) return; // Already exported?!
            done[itemid].Add(key);


            base.AppendRowInternal(itemid, key, value);
        }
    }
}
