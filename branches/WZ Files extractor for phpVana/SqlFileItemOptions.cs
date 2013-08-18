using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;

namespace WZ_Files_extractor_for_phpVana
{
    class SqlFileItemOptions
    {
        static StringBuilder Contents { get; set; }
        static int amountOfRows = 0;
        public const int maxAmountOfRows = 2000;
        static bool wroteSomething = false;

        public static void Init()
        {
            Program.isCharacterWZ = true;
            Contents = new StringBuilder();
            Contents.AppendLine(@"
DROP TABLE IF EXISTS `phpVana_itemoptions_levels`;

CREATE TABLE `phpVana_itemoptions_levels` ( 
  `id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `options` text NOT NULL,
  UNIQUE KEY `id` (`id`,`level`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
");
            Contents.AppendLine();
        }

        public static void AppendToFile(string value)
        {
            Contents.Append(value);
        }

        public static void EndOfSQL()
        {
            if (!wroteSomething) return;
            Contents = Contents.Remove(Contents.ToString().LastIndexOf(", "), 2);
            Contents.AppendLine(";");
            amountOfRows = 0;
        }

        public static void AppendToFile(int objectid, int level, string options)
        {
            wroteSomething = true;
            if (amountOfRows == 0)
            {
                Contents.AppendLine("INSERT INTO `phpVana_itemoptions_levels` (`id`, `level`, `options`) VALUES ");
            }
            amountOfRows++;

            options = MySql.Data.MySqlClient.MySqlHelper.EscapeString(options);

            Contents.AppendLine("(" + objectid + ", " + level + ", '" + options + "'), ");
            if (amountOfRows == maxAmountOfRows)
            {
                EndOfSQL();
            }
        }

        public static void createSQLFile(string filename)
        {
            if (!wroteSomething) return;


            FileInfo info = new FileInfo(filename);
            if (info.Exists)
            {
                info.Delete();
            }
            StreamWriter writer = new StreamWriter(info.OpenWrite(), UTF8Encoding.UTF8);
            writer.AutoFlush = true;
            writer.Write(Contents.ToString());
            writer.Flush();
            writer.Close();
        }
    }
}
