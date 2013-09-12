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
    public class StringsExtractor : BaseExtractor
    {
        public StringsExtractor(string extractionDir, string wzDir, WzMapleVersion version) : base(extractionDir, wzDir, version) { }


        public override void Start()
        {
            WzFile file = MapleFileCache.Instance["String"];

            {
                var equiptypes = file.WzDirectory["Eqp.img"];
                foreach (var key in (equiptypes as WzImage)["Eqp"].WzProperties)
                {
                    ExtractItems(key.ToSubProperty());
                }
            }

            {
                var list = file.WzDirectory["Etc.img"];
                ExtractItems((list as WzImage)["Etc"] as WzSubProperty);
            }

            {
                var list = file.WzDirectory["Consume.img"];
                ExtractItems(list as WzImage);
            }

            {
                var list = file.WzDirectory["Ins.img"];
                ExtractItems(list as WzImage);
            }

            {
                var list = file.WzDirectory["Cash.img"];
                ExtractItems(list as WzImage);
            }

            {
                var list = file.WzDirectory["Pet.img"];
                ExtractItems(list as WzImage);
            }

            {
                var list = file.WzDirectory["Skill.img"];
                ExtractSkills(list as WzImage);
            }

            {
                var list = file.WzDirectory["Mob.img"];
                ExtractItems(list as WzImage, "mob");
            }

            {
                var list = file.WzDirectory["Npc.img"];
                ExtractItems((list as WzImage).WzProperties, "npc");
            }

            {
                var list = file.WzDirectory["Familiar.img"];
                if (list != null)
                    ExtractItems((list as WzImage)["skill"] as WzSubProperty, "familiar_skill");
            }

            {
                var maptypes = file.WzDirectory["Map.img"];
                foreach (var key in (maptypes as WzImage).WzProperties)
                {
                    Console.WriteLine("-> {0}", key.Name);
                    ExtractMaps(key as WzSubProperty);
                }
            }

            file.Dispose();
            MapleFileCache.Instance["String"] = null;
        }


        public void ExtractItems(WzImage pImage, string pTypeName = "item")
        {
            Console.WriteLine("Extracting {0}", pImage.Name);
            foreach (var key in pImage.WzProperties.Where(val => { return val is WzSubProperty; }))
            {
                int id = Convert.ToInt32(key.Name);
                string name = key["name"].ToStringValue();
                var desc = key["desc"];
                SQLStrings.Instance.AppendRow(pTypeName, id, "name", name);
                if (desc != null && !(desc is WzNullProperty))
                    SQLStrings.Instance.AppendRow(pTypeName, id, "desc", desc.ToStringValue());
            }
        }


        public void ExtractSkills(WzImage pImage)
        {
            Console.WriteLine("Extracting {0}", pImage.Name);
            foreach (var key in pImage.WzProperties.Where(val => { return val is WzSubProperty; }))
            {
                int id = Convert.ToInt32(key.Name);

                if (key["bookName"] != null)
                {
                    string name = key["bookName"].ToStringValue();
                    SQLStrings.Instance.AppendRow("skill", id, "bname", name);
                }
                else
                {
                    string name = key["name"].ToStringValue();
                    SQLStrings.Instance.AppendRow("skill", id, "name", name);

                    var desc = key["desc"];
                    if (desc != null)
                        SQLStrings.Instance.AppendRow("skill", id, "desc", desc.ToStringValue());
                }
            }
        }

        public void ExtractItems(WzSubProperty pSubProperty, string pTypeName = "item")
        {
            Console.WriteLine("Extracting items {0}", pSubProperty.Name);
            foreach (var key in pSubProperty.WzProperties.Where(val => { return val is WzSubProperty && (val as WzSubProperty).WzProperties.Length != 0; }))
            {
                int id = Convert.ToInt32(key.Name);
                string name = key["name"].ToStringValue();
                SQLStrings.Instance.AppendRow(pTypeName, id, "name", name);

                var desc = key["desc"];
                if (desc != null)
                    SQLStrings.Instance.AppendRow(pTypeName, id, "desc", desc.ToStringValue());
            }
        }

        public void ExtractItems(IWzImageProperty[] pSubProperties, string pTypeName)
        {
            foreach (var key in pSubProperties.Where(val => { return val is WzSubProperty && (val as WzSubProperty).WzProperties.Length != 0 && val["name"] != null; }))
            {
                int id = Convert.ToInt32(key.Name);
                string name = key["name"].ToStringValue();
                SQLStrings.Instance.AppendRow(pTypeName, id, "name", name);

                var desc = key["desc"];
                if (desc != null)
                    SQLStrings.Instance.AppendRow(pTypeName, id, "desc", desc.ToStringValue());
            }
        }

        public void ExtractMaps(WzSubProperty pSubProperty)
        {
            foreach (var key in pSubProperty.WzProperties.Where(val => { return val is WzSubProperty; }))
            {
                int id = Convert.ToInt32(key.Name);

                var name = key["mapName"];
                if (name != null)
                    SQLStrings.Instance.AppendRow("map", id, "name", name.ToStringValue());

                var street = key["streetName"];
                if (street != null)
                    SQLStrings.Instance.AppendRow("map", id, "street", street.ToStringValue());

                var desc = key["mapDesc"];
                if (desc != null)
                    SQLStrings.Instance.AppendRow("map", id, "desc", desc.ToStringValue());
            }
        }
    }
}
