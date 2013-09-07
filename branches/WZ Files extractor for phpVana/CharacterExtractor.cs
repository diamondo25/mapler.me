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
    class CharacterExtractor : BaseExtractor
    {
        public static string[] STANCES = new string[] {
            "front", "back", "stand1", "stand2", "alert", "fly", "jump", "sit", "walk1", "walk2", "alert", "prone", "proneStab", 
            "rope", "ladder",
            "default",
            "backDefault",
            "heal",
            "setitem1", "setitem2", "setitem3", "setitem4", 

            "swingO1", "swingO2", "swingO3", "swingOF",
            "swingP1", "swingP2", "swingP3", "swingPF",
            "swingT1", "swingT2", "swingT3", "swingTF",

            "stabO1", "stabO2", "stabOF",
            "stabT1", "stabT2", "stabTF",
            "shoot1", "shoot2", "shootF",
        };


        public CharacterExtractor(string extractionDir, string wzDir, WzMapleVersion version) : base(extractionDir, wzDir, version, "Character") { }


        public override void Start()
        {
            WzFile file = MapleFileCache.Instance["Character"];

            string[] emoteList = new string[] {
                "angry", "bewildered", "blaze", "blink", "bowing", "cheers", "chu", "cry", "dam", "despair", "glitter", "hit", "hot", "hum",
                "love", "oops", "pain", "shine", "smile", "stunned", "troubled", "vomit", "wink",
                "default"
            };

            string tmp = exDir;
            foreach (var item in file.WzDirectory.WzImages) // 00002000.img
            {
                exDir += Path.DirectorySeparatorChar + item.Name + Path.DirectorySeparatorChar;
                currentID = Convert.ToInt32(item.Name.Replace(".img", ""));

                foreach (var stance in STANCES)
                    if (item[stance] != null)
                        HandleHairStyle(exDir, item[stance].ToSubProperty(), stance);


                exDir = RemoveFromBackDirSlash(exDir);
                exDir = RemoveFromBackDirSlash(exDir);
                item.Dispose();
            }

            tmp = exDir;
            foreach (var dir in file.WzDirectory.WzDirectories.Where(a =>
            {
                return a.Name != "Familiar" && a.Name != "Afterimage"; 
            })) // Accessory
            {
                Console.WriteLine("> {0}", dir.Name);
                exDir += Path.DirectorySeparatorChar + dir.Name; // Pet
                foreach (var item in dir.WzImages) // 01010000.img
                {
                    exDir += Path.DirectorySeparatorChar + item.Name + Path.DirectorySeparatorChar;
                    if (item.Name.IndexOf(".img") != -1)
                        currentID = int.Parse(item.Name.Remove(item.Name.Length - 4));
                    else
                        currentID = int.Parse(item.Name);

                    if (item["info"] != null)
                    {
                        HandleInfoBlock(exDir, item["info"].ToSubProperty(), "info");
                    }

                    if (dir.Name == "Weapon")
                    {
                        bool found = false;
                        for (int i = 30; i <= 53; i++)
                        {
                            if (item[i.ToString()] != null)
                            {
                                var inner = item[i.ToString()];

                                foreach (var stance in STANCES)
                                    if (inner[stance] != null) HandleStance(exDir, inner[stance].ToSubProperty(), i.ToString() + "." + stance);

                                found = true;
                            }
                        }
                        if (!found)
                        {
                            foreach (var stance in STANCES)
                                if (item[stance] != null) HandleStance(exDir, item[stance].ToSubProperty(), stance);

                        }
                    }
                    else if (dir.Name == "PetEquip")
                    {
                        for (int i = 0; i <= 300; i++)
                        {
                            int actualID = 5000000 + i;
                            if (item[actualID.ToString()] != null)
                            {
                                var inner = item[actualID.ToString()];

                                foreach (var stance in STANCES)
                                    if (item[stance] != null) HandleStance(exDir, inner[stance].ToSubProperty(), actualID + "." + stance);

                            }
                        }
                    }
                    else if (dir.Name == "Hair")
                    {
                        foreach (var stance in STANCES)
                            if (item[stance] != null) HandleHairStyle(exDir, item[stance].ToSubProperty(), stance);

                    }
                    else
                    {
                        foreach (var stance in STANCES)
                            if (item[stance] != null) HandleStance(exDir, item[stance].ToSubProperty(), stance);

                    }

                    foreach (var emote in emoteList)
                    {
                        if (item[emote] == null) continue;
                        HandleEmote(exDir, item[emote].ToSubProperty(), emote);
                    }
                    exDir = RemoveFromBackDirSlash(exDir);
                    exDir = RemoveFromBackDirSlash(exDir);

                }

                exDir = RemoveFromBackDirSlash(exDir);

                dir.Dispose();
            }
            file.Dispose();

            MapleFileCache.Instance["Character"] = null; // unload
        }

        private void HandleInfoBlock(string pDir, WzSubProperty pProp, string pPrepend = "")
        {
            ExportIfExists(pDir, pProp["icon"], pPrepend);
            ExportIfExists(pDir, pProp["iconRaw"], pPrepend);
            ExportIfExists(pDir, pProp["iconD"], pPrepend); // Androids
            ExportIfExists(pDir, pProp["iconRawD"], pPrepend);
            SaveInfo(pProp["vslot"], pPrepend);
            SaveInfo(pProp["islot"], pPrepend);

            ItemInformation.Instance.HandleInfo(currentID, pProp);
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

        private void HandleStance(string pDir, WzSubProperty pProp, string pPrepend = "")
        {
            if (pProp == null) return;

            ExportAnimatedObject(pDir, pProp, pPrepend);

        }

        private void HandleEmote(string pDir, WzSubProperty pProp, string pPrepend = "")
        {
            if (pProp == null) return;
            if (pProp.Name != "default")
            {
                if (pProp["0"] == null) return;

                ExportAnimatedObject(pDir, pProp, pPrepend);
            }
            else
            {
                foreach (var prop in pProp.WzProperties)
                {
                    ExportIfExists(pDir, prop, pPrepend);
                }
            }
        }
    }
}
