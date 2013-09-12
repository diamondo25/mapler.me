using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public abstract class IClientPacketHandlers
    {
        public virtual void HandleVersion(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte locale = pPacket.ReadByte();
            ushort version = pPacket.ReadUShort();
            ushort subversion = pPacket.ReadUShort();

            pConnection.Logger_WriteLine("Detected MapleStory version of client: {1}.{2} (locale: {0})", locale, version, subversion);
            pConnection.MapleVersion = version;

            pConnection.CharData = null; // Back to the LoginServer!!!
            if (locale != ServerMapleInfo.LOCALE)
            {
                pConnection.Logger_WriteLine("Incompatible MapleStory locale detected!!!!");
                pConnection.SendInfoText("Unsupported MapleStory client detected. Mapler.me only supports MapleStory Global version {0} at the moment.", ServerMapleInfo.VERSION); // This should _never_ happen XD (different encryption and such)
                pConnection.Disconnect();
            }
            else if (version > ServerMapleInfo.VERSION)
            {
                pConnection.Logger_WriteLine("MapleStory client of user is outdated/incorrect. Disconnect.");
                pConnection.SendInfoText("Your MapleStory client seems outdated. Update your client in order to use the Mapler.me service.\r\nVersion identified: {0}\r\nSupported version: {1}", version, ServerMapleInfo.VERSION);
                pConnection.Disconnect();
            }
            else if (version < ServerMapleInfo.VERSION)
            {
                pConnection.Logger_WriteLine("MapleStory client of user is more up-to-date than Mapler.me service!!!");
                pConnection.SendInfoText("As your client is more up-to-date than the Mapler.me service, you are unable to use the Mapler.me service at this time.\r\nCheck the Mapler.me website and/or Twitter (@maplerme) for updates!\r\n\r\nCurrently supported version: {0}\r\nYour version: {1}", ServerMapleInfo.VERSION, version);
                pConnection.Disconnect();
            }

        }


        public virtual void HandleCharacterLoadRequest(ClientConnection pConnection, MaplePacket pPacket)
        {
            if (!(pConnection.ConnectedToPort >= 8585 && pConnection.ConnectedToPort <= 8600))
            {
                pConnection.Logger_WriteLine("Ignoring Character Load Request; not connected to a channel server");
                return;
            }

            int characterid = pPacket.ReadInt();
            byte[] machineid = pPacket.ReadBytes(16);
            pConnection.MachineID = machineid;

            if (pConnection.UserID == -1)
            {
                SessionRestartCache.Instance.TryRestartSession(pConnection, characterid, machineid);
            }
        }

        public virtual void HandleKeymapUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            int mode = pPacket.ReadInt();
            if (mode == 0)
            {
                Dictionary<byte, KeyValuePair<byte, int>> modifieds = new Dictionary<byte, KeyValuePair<byte, int>>();
                int amount = pPacket.ReadInt();
                for (int i = 0; i < amount; i++)
                {
                    int idx = pPacket.ReadInt();
                    byte type = pPacket.ReadByte();
                    int value = pPacket.ReadInt();

                    if (idx < 0 || idx > ServerMapleInfo.KEYMAP_SLOTS)
                    {
                        pConnection.Logger_WriteLine("ERROR: Keymap contained invalid index!");
                        return;
                    }

                    if (modifieds.ContainsKey((byte)idx)) continue; // -.-''

                    modifieds.Add((byte)idx, new KeyValuePair<byte, int>(type, value));
                }

                if (modifieds.Count == 0) return;

                StringBuilder sb = new StringBuilder();
                sb.Append("UPDATE character_keymaps SET ");
                foreach (var kvp in modifieds)
                    sb.AppendFormat("map_{0}_type = {1}, map_{0}_value = {2},", kvp.Key, kvp.Value.Key, kvp.Value.Value);

                sb.Append(" WHERE character_id = " + pConnection.CharacterInternalID);

                MySQL_Connection.Instance.RunQuery(sb.ToString().Replace(", WHERE", " WHERE"));

            }
        }
    }
}
