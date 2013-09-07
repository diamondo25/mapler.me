using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;
using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

namespace WZ_Files_extractor_for_phpVana
{
    class EtcExporter : BaseExtractor
    {
        public EtcExporter(string extractionDir, string wzDir, WzMapleVersion version) : base(extractionDir, wzDir, version) { }


        public override void Start()
        {
            WzFile file = MapleFileCache.Instance["Etc"];
            WzDirectory familiarscwz = MapleFileCache.Instance["Character"].GetObjectFromPath("Character.wz/Familiar") as WzDirectory;
            WzFile mobfile = MapleFileCache.Instance["Mob"];

            familiarscwz.ParseImages();

            string familiarDir = exDir + "Character" + Path.DirectorySeparatorChar + "Familiar" + Path.DirectorySeparatorChar;
            //if (false)
            {
                foreach (WzSubProperty prop in (file.WzDirectory["FamiliarInfo.img"] as WzImage).WzProperties)
                {
                    int famid = int.Parse(prop.Name);
                    int mobid = prop["mob"].ToInt();
                    string tempdir = familiarDir + string.Format("{0:D7}.img", famid) + Path.DirectorySeparatorChar;

                    WzSubProperty charwzProp = familiarscwz.GetImageByName(string.Format("{0}.img", famid))["info"] as WzSubProperty;
                    WzSubProperty skillSubProp = prop["skill"] as WzSubProperty;
                    FamiliarInfoSQL.Instance.AddFamiliar(famid, prop["consume"].ToInt(), mobid, charwzProp["grade"].ToInt(), charwzProp["range"].ToInt(), skillSubProp == null ? 0 : skillSubProp["id"].ToInt());

                    // Export images, too

                    WzImage mobimg = mobfile.WzDirectory[string.Format("{0:D7}.img", mobid)] as WzImage;

                    if (mobimg["info"] != null && mobimg["info"]["link"] != null)
                    {
                        // ---
                        Console.WriteLine("Nekson leik links");
                        mobimg = mobfile.WzDirectory[string.Format("{0:D7}.img", mobimg["info"]["link"].ToInt())] as WzImage;
                    }

                    if (mobimg["stand"] != null)
                    {
                        WzSubProperty subprop = mobimg["stand"] as WzSubProperty;
                        ExportIfExists(tempdir, subprop["0"], "stand");
                        foreach (var prop2 in subprop["0"].WzProperties)
                        {
                            ExportIfExists(tempdir, prop2, "stand.0");
                        }
                    }
                    else if (mobimg["fly"] != null)
                    {
                        WzSubProperty subprop = mobimg["fly"] as WzSubProperty;
                        ExportIfExists(tempdir, subprop["0"], "stand");
                        foreach (var prop2 in subprop["0"].WzProperties)
                        {
                            ExportIfExists(tempdir, prop2, "stand.0");
                        }
                    }
                }

            }

            this.currentID = 90000000;
            foreach (WzSubProperty prop in (file.WzDirectory["SetItemInfo.img"] as WzImage).WzProperties)
            {
                ExportProps(prop, prop.Name);
            }
        }

    }

    class FamiliarInfoSQL : BaseSQLFile
    {
        public static FamiliarInfoSQL Instance { get; private set; }

        public static void Init()
        {
            Instance = new FamiliarInfoSQL();
        }


        public FamiliarInfoSQL()
        {
            this._buffer = new StringBuilder(@"
DROP TABLE IF EXISTS `phpVana_familiar_info`;

CREATE TABLE `phpVana_familiar_info` ( 
  `familiar_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `mob_id` int(10) unsigned NOT NULL,
  `grade` tinyint(3) unsigned NOT NULL,
  `range` smallint unsigned NOT NULL,
  `skill_id` smallint unsigned NOT NULL,
  UNIQUE KEY `familiar_id` (`familiar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
");

            this._insert = "INSERT INTO `phpVana_familiar_info` (`familiar_id`, `item_id`, `mob_id`, `grade`, `range`, `skill_id`) VALUES ";
        }

        public void AddFamiliar(int pFamiliarID, int pItemID, int pMobID, int pGrade, int pRange, int pSkillID)
        {
            base.AppendRowInternal(pFamiliarID, pItemID, pMobID, pGrade, pRange, pSkillID);
        }
    }
}
