using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class ClientPacketHandlers
    {
        public const byte LatestLocale = 8;
        public const ushort LatestMajorVersion = 134; // Packet changes do not occur per minor version

        public static void HandleVersion(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte locale = pPacket.ReadByte();
            ushort version = pPacket.ReadUShort();
            ushort subversion = pPacket.ReadUShort();

            pConnection.Logger_WriteLine("Detected MapleStory version of client: {1}.{2} (locale: {0})", locale, version, subversion);
            pConnection.MapleVersion = version;
            pConnection.CharData = null; // Back to the LoginServer!!!
            if (locale != LatestLocale)
            {
                pConnection.Logger_WriteLine("Incompatible MapleStory locale detected!!!!");
                pConnection.SendInfoText("Unsupported MapleStory client detected. Mapler.me only supports MapleStory Global version {0} at the moment.", LatestMajorVersion); // This should _never_ happen XD (different encryption and such)
                pConnection.Disconnect();
            }
            else if (version > LatestMajorVersion)
            {
                pConnection.Logger_WriteLine("MapleStory client of user is outdated/incorrect. Disconnect.");
                pConnection.SendInfoText("Your MapleStory client seems outdated. Update your client in order to use the Mapler.me service.\r\nVersion identified: {0}\r\nSupported version: {1}", version, LatestMajorVersion);
                pConnection.Disconnect();
            }
            else if (version < LatestMajorVersion)
            {
                pConnection.Logger_WriteLine("MapleStory client of user is more up-to-date than Mapler.me service!!!");
                pConnection.SendInfoText("As your client is more up-to-date than the Mapler.me service, you are unable to use the Mapler.me service at this time.\r\nCheck the Mapler.me website and/or Twitter (@maplerme) for updates!\r\n\r\nCurrently supported version: {0}\r\nYour version: {1}", LatestMajorVersion, version);
                pConnection.Disconnect();
            }

        }
    }
}
