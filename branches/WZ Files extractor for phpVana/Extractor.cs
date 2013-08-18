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
    public class Extractor
    {
        public string currentDir { get; set; }
        public string currentDirdunno { get; set; }
        public string exDir { get; set; }
        public string wzDir { get; set; }
        public WzMapleVersion Version { get; set; }
        public List<string> correctProperties = new List<string>(new string[] { "info", "default", "stand1", "stand2", "front", "back", "BackGround", "Mark", "walk1", "walk2", "sit", "blink",
        
        // Face list
        "angry", "bewildered", "blaze", "blink", "bowing", "cheers", "chu", "cry", "dam", "despair", "glitter",
        "hit", "hot", "hum", "love", "oops", "pain", "qBlue", "shine", "smile", "stunned", "troubled", "vomit", "wink",
        
        
        });
        public List<string> correctSubProperties = new List<string>(new string[] {"info", "vslot", "hair", "hairOverHead", "hairBelowBody", "hairShade", "default", "cape", "shield",
                                                         "shoes", "lGlove", "rGlove", 
                                                         "stand1", "stand2", 
                                                         "walk1", "walk2", 
                                                         "sit",
                                                         "origin", "X", "Y", "z", "pants", "mail",
                                                         "mailArm", "map", "navel", "weapon", "hand", "shoesTop", "mailChestOverHighest", "brow",
                                                         "pantsOverMailChest", "mailChest", "pantsOverShoesBelowMailChest", "shoesOverPants",
                                                         "mailChestOverPants", "pants", "pantsBelowShoes", "shoes", "head", "front", "back", "face", "nameTag",
                                                         "e", "w", "c", "pet", "medal",
                                                         "defaultAc", "capeArm", "shield",
                                                         "BackGround", "Mark", "Animal", "Etc", "Letter", "Pattern", "Plant"});

        public Extractor(string extractionDir, string wzDir, WzMapleVersion version)
        {
            Version = version;
            exDir = extractionDir;
            currentDir = extractionDir + "\\";
            this.wzDir = wzDir;
        }

        public void startExtractCharacters()
        {
            WzFile file = new WzFile(wzDir + @"\Character.wz", Version);
            //WzFile file = new WzFile(wzDir + @"\Data - kopie.wz", Version);
            file.ParseWzFile();
            //Console.WriteLine("Version: {0}", file.);
            
            string tempDir = currentDir;
            WzDirectory loldir = file.WzDirectory.GetDirectoryByName("Character");
            if (loldir == null)
            {
                loldir = file.WzDirectory;
            }
            loldir.ParseImages();

            foreach (WzImage img in loldir.WzImages)
            {
                currentDir += img.Name + "\\";
                parseImage(img);
                currentDir = tempDir;
            }

            foreach (WzDirectory dir in loldir.WzDirectories)
            {
                currentDir += dir.Name + "\\";
                parseDir(dir);
                currentDir = tempDir;
            }

        }

        int deep = 0;

        public void parseDir(WzDirectory dir)
        {
            deep++;
            makeRealDir();
            string tempDir = currentDir;
            Form1.Instance.BeginInvoke((MethodInvoker)delegate
            {
                Form1.Instance.progress.Maximum += dir.WzDirectories.Length;
            });

            foreach (WzDirectory dirInDir in dir.WzDirectories)
            {
                Form1.Instance.BeginInvoke((MethodInvoker)delegate
                {
                    Form1.Instance.progress.Value++;
                });
                currentDir += dirInDir.Name + "\\";
                parseDir(dirInDir);
                currentDir = tempDir;
            }

            Form1.Instance.BeginInvoke((MethodInvoker)delegate
            {
                Form1.Instance.progress.Maximum += dir.WzImages.Length;
            });
            foreach (WzImage img in dir.WzImages)
            {
                Form1.Instance.BeginInvoke((MethodInvoker)delegate
                {
                    Form1.Instance.progress.Value++;
                });
                currentDir += img.Name + "\\";
                parseImage(img);
                currentDir = tempDir;
            }
            deep--;
        }

        public void parseImage(WzImage image)
        {
            image.ParseImage();
            deep++;
            makeRealDir();
            string tempDir = currentDir;
            string tempDir2 = currentDirdunno;
            /*
            Form1.Instance.BeginInvoke((MethodInvoker)delegate
            {
                Form1.Instance.progress.Maximum += image.WzProperties.Length;
            });
             * */

            foreach (IWzImageProperty prop in image.WzProperties)
            {
                /*
                    Form1.Instance.BeginInvoke((MethodInvoker)delegate
                    {
                        Form1.Instance.progress.Value++;
                    });
                 * */
                currentDir += prop.Name + ".";
                currentDirdunno += prop.Name + ".";
                parseProperties(prop, 1);
                currentDir = tempDir;
                currentDirdunno = tempDir2;
            }
            image.UnparseImage();
            image.Dispose();
            deep--;
        }

        public void parseProperties(IWzImageProperty iwip, int proploop)
        {
            switch (iwip.PropertyType)
            {
                case WzPropertyType.SubProperty: parseSubProperty(iwip, proploop); break;
                case WzPropertyType.String: parseString(iwip); break;
                case WzPropertyType.Canvas: parseImage(iwip); break;
                case WzPropertyType.Vector: parseVector(iwip); break;
            }
        }

        public void parseSubProperty(IWzImageProperty iwip, int propLoop)
        {
            if (!currentDir.EndsWith("\\") && !currentDir.EndsWith("."))
            {
                currentDir += (Program.isCharacterWZ ? "." : "\\");
            }
            if (!currentDirdunno.EndsWith("\\") && !currentDirdunno.EndsWith("."))
            {
                currentDirdunno += (Program.isCharacterWZ ? "." : "\\");
            }
            if (isCorrectProperty(iwip.Name) || isCorrectSubProperty(iwip.Name))
            {
                string tempDir = currentDir;
                string tempDir2 = currentDirdunno;
                foreach (IWzImageProperty prop in iwip.WzProperties)
                {
                    currentDir += prop.Name + ".";
                    currentDirdunno += prop.Name + ".";
                    parseProperties(prop, propLoop + 1);
                    currentDir = tempDir;
                    currentDirdunno = tempDir2;
                }
            }
        }

        public void parseVector(IWzImageProperty iwip)
        {
            int x = ((WzVectorProperty)iwip).X.ToInt();
            int y = ((WzVectorProperty)iwip).Y.ToInt();
            int itemId = getImageItemid();
            if (itemId > 0)
            {
                if (!currentDir.EndsWith("_"))
                {
                    currentDir += "_";
                }
                currentDir += iwip.Name;
                SQLData.Instance.AppendRow(itemId, getInfo().TrimEnd('_') + "_X", x.ToString());
                SQLData.Instance.AppendRow(itemId, getInfo().TrimEnd('_') + "_Y", y.ToString());
            }
        }

        public void parseImage(IWzImageProperty iwip)
        {
            if (!currentDir.EndsWith("\\") || !currentDir.EndsWith("."))
            {
                currentDir += (Program.isCharacterWZ ? "." : "\\");
            }

            if (!currentDirdunno.EndsWith("\\") || !currentDirdunno.EndsWith("."))
            {
                currentDir += (Program.isCharacterWZ ? "." : "\\");
                currentDirdunno += (Program.isCharacterWZ ? "." : "\\");
            }

            if (!Program.isCharacterWZ)
            {
                makeRealDir();
            }



            if (false && !File.Exists(correctName(currentDir + iwip.Name + ".png", true)))
            {
                iwip.ToBitmap().Save(correctName(currentDir + iwip.Name + ".png", true), System.Drawing.Imaging.ImageFormat.Png);
            }



            string tempDir = currentDir;
            string tempDir2 = currentDirdunno;
            foreach (IWzImageProperty prop in iwip.WzProperties)
            {
                currentDir += prop.Name + ".";
                currentDirdunno += prop.Name + ".";
                parseProperties(prop, 1);
                currentDir = tempDir;
                currentDirdunno = tempDir2;
            }

            /*
            if (iwip["origin"] != null)
            {
                parseVector(iwip["origin"], "origin");
            }

            if (iwip["map"] != null)
            {
                parseVector(iwip["map"], "map_brow");
            }
            */
        }

        public void parseString(IWzImageProperty iwip)
        {
            int itemId = getImageItemid();
            if (itemId > 0)
            {
                if (!currentDir.EndsWith("_"))
                {
                    currentDir += "_";
                }
                currentDir += iwip.Name;
                SQLData.Instance.AppendRow(itemId, getInfo().TrimEnd('_'), iwip.ToStringValue());
            }
        }

        List<string> notOKProp = new List<string>();
        public bool isCorrectProperty(string what)
        {
            bool k = correctProperties.Contains(what);
            if (!k)
            {
                if (!notOKProp.Contains(what))
                {
                    Console.WriteLine("[PROPNOTOK] {0}", what);
                    notOKProp.Add(what);
                }
            }
            return k;
        }

        List<string> notOKSubProp = new List<string>();
        public bool isCorrectSubProperty(string what)
        {
            bool k = correctSubProperties.Contains(what);
            if (!k)
            {
                if (isNumeric(what))
                    return true;
                if (!notOKSubProp.Contains(what))
                {
                    Console.WriteLine("[SUBPROPNOTOK] {0}", what);
                    notOKSubProp.Add(what);
                }

            }
            return k;
        }

        public void makeRealDir()
        {
            /*
            string[] dirs = currentDir.Split('\\');
            string curDir = "";
            foreach (string dir in dirs)
            {
                curDir += dir + "\\";
                if (!Directory.Exists(curDir))
                {*/
                    //Directory.CreateDirectory(currentDir);
               /* }
            }*/
        }

        public string correctName(string what, bool isImg)
        {
            if (isImg)
            {
                what = what.Replace("....", ".");
                what = what.Replace("...", ".");
                what = what.Replace("..", ".");
                what = what.Replace("_.", ".");
            }
            else
            {
                what = what.Replace("_____", "_");
                what = what.Replace("____", "_");
                what = what.Replace("___", "_");
                what = what.Replace("__", "_");
                what = what.Replace("_.", ".");
            }

            return what;
        }

        public string getInfo()
        {
            string correctMap = getCorrectMap();
            return correctName(correctMap.Replace(".", "_"), false);
        }

        public string getCorrectMap()
        {
            string tmp = currentDirdunno.Replace(exDir + "\\", "");
            /*
            string[] tmp2 = tmp.Split('\\');
            tmp = "";
            for (int i = 1; i < tmp2.Length; i++)
            {
                tmp += tmp2[i] + "\\";
            }
            tmp = tmp.Substring(0, tmp.LastIndexOf('\\'));
            */
            return tmp;
        }

        public int getImageItemid()
        {
            string correctMap = getCorrectMap();
            string ret = getItemFolder();
            if (ret != "")
            {
                if (isNumeric(ret.Replace(".img", "")))
                {
                    return Convert.ToInt32(ret.Replace(".img", ""));
                }
                else if (isNumeric(ret))
                {
                    return Convert.ToInt32(ret);
                }
                string[] folders = correctMap.Split('\\');
                foreach (string folder in folders)
                {
                    if (folder == ret && isNumeric(folder.Replace(".img", "")))
                    {
                        return Convert.ToInt32(folder.Replace(".img", ""));
                    }
                    else if (isNumeric(folder.Replace(".img", "")))
                    {
                        return Convert.ToInt32(folder.Replace(".img", ""));
                    }
                    else if (!Program.isCharacterWZ && isNumeric(folder))
                    {
                        return Convert.ToInt32(folder);
                    }
                }
            }
            else
            {
                Console.WriteLine("Nope. {0}", correctMap);
            }
            return -1;
        }

        public static bool isNumeric(string what)
        {
            if (what == "-") return false; // ugg
            foreach (char c in what)
            {
                if (!(c >= '0' && c <= '9') && c != '-') return false;
            }
            return true;
        }

        public string getItemFolder()
        {
            string[] folders = currentDir.Split('\\');
            for (int i = folders.Length - 1; i != 0; i--)
            {
                if (!Program.isCharacterWZ && isNumeric(folders[i].Substring(0, 1)))
                {
                    return folders[i];
                }
                else if (isNumeric(folders[i].Replace(".img", "")))
                {
                    return folders[i];
                }
            }
            Console.WriteLine("Nooope: {0}", currentDir);
            return "";
        }
    }
}
