using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Net;
using System.IO;

using System.Diagnostics;
using System.Threading;


namespace MPLRServer
{
    class GMSKeys
    {
        private static Dictionary<ushort, byte[]> MapleStoryGlobalKeys = new Dictionary<ushort, byte[]>();
        public static ushort LatestVersion = 0;

        private static void InitByContents(string pContents)
        {
            string[] lines = pContents.Split(new string[] { "\r\n" }, StringSplitOptions.RemoveEmptyEntries);
            for (int i = 0; i < lines.Length; i += 2)
            {
                ushort version = ushort.Parse(lines[i]);
                if (version > LatestVersion) LatestVersion = version;

                string tmpkey = lines[i + 1];
                byte[] realkey = new byte[32];
                int tmp = 0;
                for (int j = 0; j < 4 * 8 * 2; j += 4 * 2)
                {
                    realkey[tmp] = byte.Parse(tmpkey[j] + "" + tmpkey[j + 1], System.Globalization.NumberStyles.HexNumber);
                    tmp += 4;
                }
                MapleStoryGlobalKeys.Add(version, realkey);
            }
            
        }

        public static void Initialize()
        {
            MapleStoryGlobalKeys.Clear();
            try
            {
                if (File.Exists("noupdate.txt")) throw new Exception(); // Trigger offline file loading

                HttpWebRequest req = WebRequest.Create("http://direct.craftnet.nl/app_updates/get_keys.php") as HttpWebRequest;
                req.Proxy = null;
                req.Timeout = 2000; // 2 seconds

                using (HttpWebResponse response = req.GetResponse() as HttpWebResponse)
                {
                    using (StreamReader sr = new StreamReader(response.GetResponseStream()))
                    {
                        string responseText = sr.ReadToEnd();
                        InitByContents(responseText);
                        File.WriteAllText("cached_keys.txt", responseText);
                    }
                }
            }
            catch
            {
                // Fail, w/e
                if (File.Exists("cached_keys.txt"))
                    InitByContents(File.ReadAllText("cached_keys.txt"));
                else
                {
                    Logger.WriteLine("!!!!!!!!!!!!!!!! COULD NOT GET KEYS");
                }
            }

            Logger.WriteLine("[GMSKeys] Found version {0}", LatestVersion);
        }

        public static byte[] GetKeyForVersion(ushort pVersion = 0)
        {
            if (pVersion == 0)
                pVersion = LatestVersion;
            // Get first version known
            for (; pVersion > 0; pVersion--)
            {
                if (MapleStoryGlobalKeys.ContainsKey(pVersion))
                {
                    return MapleStoryGlobalKeys[pVersion];
                }
            }
            return null;
        }
    }
}
