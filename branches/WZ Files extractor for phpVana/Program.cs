using System;
using System.Collections.Generic;
using System.Linq;
using System.Windows.Forms;
using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

namespace WZ_Files_extractor_for_phpVana
{
    class Program
    {
        public static Form1 MainForm { get; set; }
        public static bool isCharacterWZ { get; set; }

        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(MainForm = new Form1());
            /*
             * */
            /*
            Console.WriteLine("WZ Dir:");
            string wzDir = Console.ReadLine();
            Console.WriteLine("Extraction dir:");
            string exDir = Console.ReadLine();

            Console.WriteLine("Maple version (GMS, BMS, EMS):");
            WzMapleVersion version = WzMapleVersion.GENERATE;
            switch (Console.ReadLine().ToLower())
            {
                case "gms": version = WzMapleVersion.GMS; break;
                case "bms": version = WzMapleVersion.BMS; break;
                case "ems": version = WzMapleVersion.EMS; break;
                default: Console.WriteLine("Unknown type."); Console.ReadKey(); return;
            }
            Extractor ex = new Extractor(@"D:\websites\wvsbeta\images\characterwz", @"C:\Nexon\Wizet MapleStory", WzMapleVersion.CLASSIC);
            ex.startExtractCharacters();

            SqlFile.createSQLFile(@"D:\websites\wvsbeta\images\Data.sql");
            */
        }
    }
}
