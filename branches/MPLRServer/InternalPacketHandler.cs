using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class InternalPacketHandler
    {
        public static void HandleServerConnectionStatus(ClientConnection pConnection, MaplePacket pPacket)
        {
            if (pPacket.ReadBool())
            {
                string ip = pPacket.ReadString();
                ushort port = pPacket.ReadUShort();
                pConnection.Logger_WriteLine("- Client got connection with MapleStory server @ {0}:{1}", ip, port);
                if (port == 8484)
                {
                    pConnection.SendInfoText("Mapler.me is awaiting account check! Happy mapling!");
                }
                else
                {
                    pConnection.SendInfoText("You successfully connected, or are in the Cash Shop!");
                }
            }
            else
            {
                pConnection.Logger_WriteLine("- Client lost connection with MapleStory server");
                pConnection.SendInfoText("Maplestory is closed, or not connected properly.");
            }
        }
    }
}
