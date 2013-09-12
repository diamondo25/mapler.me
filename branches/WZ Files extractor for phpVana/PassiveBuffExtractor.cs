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
    public class PassiveBuffExtractor : BaseExtractor
    {
        public PassiveBuffExtractor(string extractionDir, string wzDir, WzMapleVersion version) : base(extractionDir, wzDir, version, "Skills") { }


        public override void Start()
        {
            WzFile file = MapleFileCache.Instance["Skill"];

            foreach (var key in file.WzDirectory.GetChildImages().Where(image => { return image.Name[0] >= '0' && image.Name[0] <= '9' && image["skill"] != null; })) // Only jobskills
            {
                Console.WriteLine("Job {0}", key.Name);
                ExtractSkills(key["skill"] as WzSubProperty);
            }

        }

        public void ExtractSkills(WzSubProperty pSubProperty)
        {
            var parent = pSubProperty.Parent as WzImage;

            int jobid = currentID = 0;
            try
            {
                jobid = currentID = Convert.ToInt32(parent.Name.Replace(".img", ""));
            }
            catch
            {
                return;
            }
            if (parent["info"] != null && parent["info"]["icon"] != null)
            {
                ExportIfExists(exDir + jobid + Path.DirectorySeparatorChar, parent["info"]["icon"], "info");
            }

            foreach (var key in pSubProperty.WzProperties.Where(val => { return val is WzSubProperty && val["disabled"] == null; }))
            {

                int id = Convert.ToInt32(key.Name);

                currentID = id;

                ExportIfExists(exDir + jobid + Path.DirectorySeparatorChar + currentID + Path.DirectorySeparatorChar, key["icon"]);
                ExportIfExists(exDir + jobid + Path.DirectorySeparatorChar + currentID + Path.DirectorySeparatorChar, key["iconDisabled"]);
                ExportIfExists(exDir + jobid + Path.DirectorySeparatorChar + currentID + Path.DirectorySeparatorChar, key["iconMouseOver"]);

                WzSubProperty common = key["common"] as WzSubProperty;
                WzSubProperty info = key["info"] as WzSubProperty;
                WzSubProperty level = key["level"] as WzSubProperty;
                if (common != null && common["maxLevel"] != null)
                {
                    SQLStrings.Instance.AppendRow("skill", currentID, "mlvl", common["maxLevel"].WzValue.ToString());
                }
                else if (level != null)
                {
                    int maxlvl = 0;
                    foreach (var sub in level.WzProperties)
                    {
                        int curlvl = Convert.ToInt32(sub.Name);
                        if (curlvl > maxlvl) maxlvl = curlvl;
                    }
                    if (maxlvl != 0)
                        SQLStrings.Instance.AppendRow("skill", currentID, "mlvl", maxlvl.ToString());
                }

                if (info != null)
                {

                    if (info["type"] != null)
                    {
                        if (info["type"].ToInt() != 50 && info["type"].ToInt() != 16)
                        {
                            //Console.WriteLine("Not a passive skill: {0}", info["type"].ToInt());
                            continue;
                        }
                    }
                    else continue;
                }

                if (common != null)
                {
                    // Console.WriteLine("Passive Skill ID {0}", id);
                    if (common["mhpR"] == null && common["mmpR"] == null &&
                        /* common["madX"] == null && common["padX"] == null && */
                        common["strX"] == null && common["dexX"] == null &&
                        common["intX"] == null && common["lukX"] == null
                        /* &&
                        common["damR"] == null && common["bdR"] == null &&
                        common["pddX"] == null && common["mddX"] == null
                        */
                        )
                    {
                        //Console.WriteLine("Not a HP/MP Rate skill");
                        string derp = "";
                        foreach (var d in common.WzProperties)
                        {
                            derp += d.Name + " | ";
                        }
                       // Console.WriteLine(derp);
                        continue;
                    }
                    else
                    {
                        // Prolly got one of those above...
                        string derp = "";
                        foreach (var d in common.WzProperties)
                        {
                            derp += d.Name + "=" + d.WzValue.ToString() + ";";
                        }
                        derp = derp.TrimEnd(';');
                        //Console.WriteLine("!!! -> {0}", derp);

                        SQLStrings.Instance.AppendRow("skill", currentID, "buff", derp);
                    }
                }

                //Console.WriteLine("-------------");
            }
        }
    }
}
