using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;
using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

using System.Windows.Forms;

namespace WZ_Files_extractor_for_phpVana
{
    class ItemsExtractor : BaseExtractor
    {
        public ItemsExtractor(string extractionDir, string wzDir, WzMapleVersion version) : base(extractionDir, wzDir, version, "Inventory") { }

        public override void Start()
        {
            WzFile file = MapleFileCache.Instance["Item"];
            
            {
                List<string> types = new List<string>();
                foreach (var item in file.WzDirectory.GetImageByName("ItemOption.img").WzProperties) // 00000001
                {
                    int id = Convert.ToInt32(item.Name);
                    string desc = "";
                    if (item["info"] != null)
                    {
                        desc = item["info"]["string"].ToStringValue();
                    }

                    SQLStrings.Instance.AppendRow("item_option", id, "desc", desc);

                    foreach (var level in item["level"].WzProperties) // 00000001
                    {
                        string optionlist = "";
                        foreach (var option in level.WzProperties)
                        {
                            if (optionlist != "")
                                optionlist += ';';
                            optionlist += option.Name + '=' + option.WzValue.ToString();
                        }
                        //Console.WriteLine("Optionlist: {0}", optionlist);
                        SqlFileItemOptions.AppendToFile(id, Convert.ToInt32(level.Name), optionlist);
                    }

                }
            }

            foreach (var key in new string[] { 
                "Cash", "Consume", "Install", "Etc"
            })
            {
                Console.WriteLine("Block {0}", key);
                HandleItemCategory(file[key] as WzDirectory);
            }
            
            Console.WriteLine("Block Pet");
            HandlePets(file["Pet"] as WzDirectory);

        }

        private void HandleItemCategory(WzDirectory pDir)
        {
            string tmp = exDir;
            exDir += Path.DirectorySeparatorChar + pDir.Name; // Etc
            foreach (var collection in pDir.GetChildImages()) // 0400.img
            {
                exDir += Path.DirectorySeparatorChar + collection.Name;
                bool isNebuliteDir = collection.Name == "0306.img";
                foreach (var item in collection.WzProperties.Where(p => { return p is WzSubProperty; })) // 04000000
                {
                    exDir += Path.DirectorySeparatorChar + item.Name + Path.DirectorySeparatorChar;

                    currentID = Convert.ToInt32(item.Name);

                    if (item["info"] != null)
                    {
                        ExportIfExists(exDir, item["info"]["icon"], "info");
                        ExportIfExists(exDir, item["info"]["iconRaw"], "info");
                        ExportIfExists(exDir, item["info"]["iconD"], "info");
                        ExportIfExists(exDir, item["info"]["iconRawD"], "info");
                        ItemInformation.Instance.HandleInfo(currentID, item["info"].ToSubProperty());
                    }
                    if (item["spec"] != null)
                    {
                        if (item["spec"]["slotCount"] != null)
                            SQLStrings.Instance.AppendRow("item_spec", currentID, "slotcount", item["spec"]["slotCount"].ToInt().ToString());
                        if (item["spec"]["slotPerLine"] != null)
                            SQLStrings.Instance.AppendRow("item_spec", currentID, "slotperline", item["spec"]["slotPerLine"].ToInt().ToString());
                        if (item["spec"]["type"] != null)
                            SQLStrings.Instance.AppendRow("item_spec", currentID, "type", item["spec"]["type"].ToInt().ToString());
                    }
                    if (item["effect"] != null)
                    {
                        foreach (var stance in CharacterExtractor.STANCES)
                            if (item["effect"][stance] != null)
                                HandleHairStyle(exDir, item["effect"][stance].ToSubProperty(), "effect." + stance);
                    }
                    if (isNebuliteDir && item["socket"] != null)
                    {
                        // Extract moar options
                        string description = item["socket"]["string"] != null ? item["socket"]["string"].ToStringValue() : "";

                        string optionlist = "";
                        foreach (var option in item["socket"]["option"].WzProperties)
                        {
                            if (optionlist != "")
                                optionlist += ';';
                            optionlist += option["optionString"].ToStringValue() + '=' + option["level"].ToInt();
                        }

                        SqlFileItemSocket.Instance.AppendRow(currentID, description, optionlist);
                    }

                    exDir = RemoveFromBackDirSlash(exDir);
                    exDir = RemoveFromBackDirSlash(exDir);
                }
                exDir = RemoveFromBackDirSlash(exDir);
            }
            exDir = RemoveFromBackDirSlash(exDir);
        }

        private void HandlePets(WzDirectory pDir)
        {
            string tmp = exDir;
            exDir += Path.DirectorySeparatorChar + pDir.Name; // Pet
            foreach (var item in pDir.GetChildImages()) // 5000000.img
            {
                exDir += Path.DirectorySeparatorChar + item.Name + Path.DirectorySeparatorChar;
                int itemid = Convert.ToInt32(item.Name.Remove(item.Name.IndexOf('.')));
                currentID = itemid;
                if (item["info"] != null)
                {
                    ExportIfExists(exDir, item["info"]["icon"], "info");
                    ExportIfExists(exDir, item["info"]["iconRaw"], "info");
                    ExportIfExists(exDir, item["info"]["iconD"], "info"); // Dead pets
                    ExportIfExists(exDir, item["info"]["iconRawD"], "info");
                    ItemInformation.Instance.HandleInfo(currentID, item["info"].ToSubProperty());
                }
                if (item["stand0"] != null)
                {
                    ExportIfExists(exDir, item["stand0"]["0"], "stand0");
                    SaveVector(item["stand0"]["0"]["origin"] as WzVectorProperty, "stand0.0");
                }

                exDir = RemoveFromBackDirSlash(exDir);
                exDir = RemoveFromBackDirSlash(exDir);
            }
            exDir = RemoveFromBackDirSlash(exDir);
        }


        private void HandleHairStyle(string pDir, WzSubProperty pProp, string pPrepend = "")
        {
            if (pProp == null) return;

            if (pProp["0"] == null)
            {
                if (pProp.WzProperties != null)
                {
                    foreach (var prop in pProp.WzProperties) // backDefault/backHair
                    {
                        ExportIfExists(pDir, prop, pPrepend);

                        ExportAnimatedObject(pDir, prop, pPrepend + "." + prop.Name);

                    }
                }
            }
            else
            {
                ExportAnimatedObject(pDir, pProp, pPrepend);
            }
        }
    }
}
