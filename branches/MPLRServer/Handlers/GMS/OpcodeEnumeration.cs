namespace MPLRServer
{
#if LOCALE_GMS
    public enum ServerOpcodes : ushort
    {
        LOGIN_NORMAL = 0x0000,
        LOGIN_AFTER_WORLDSELECT = 0x0002,
        CHARACTER_CHOOSE = 0x000B,
        CHARACTER_DELETE = 0x000E,

        CONNECT_TO_SERVER = 0x0010,
        PING = 0x0011,

        INVENTORY_UPDATE = 0x0025,
        INVENTORY_SLOTS_UPDATE,
        STAT_UPDATE,
        SKILL_UPDATE = 0x002C,

        MESSAGE = 0x0033,

        BUDDYLIST = 0x005D,
        GUILD = 0x005F,
        ALLIANCE,

        FAMILIARS = 0x0081,

        ABILITY_UPDATE = 0x00D2,

        MAPLEPOINTS = 0x00E6,

        SKILL_MACROS = 0x010B,
        CHANGE_MAP,

        SPAWN_PLAYER = 0x0143,

        SPAWN_ANDROID = 0x0177,

        KEYMAP_UPDATE = 0x030E,
    }

    public enum ClientOpcodes : ushort
    {
        VERSION_INFO = 0x003F,
        SELECT_CHANNEL = 0x0043,
        CHARACTER_LOAD = 0x0027,
        PONG = 0x0046,
        CHANGE_CHANNEL_REQUEST = 0x0052,

        WHISPER = 0x1100,

        KEYMAP_UPDATE = 0x0134
    }
#endif
}