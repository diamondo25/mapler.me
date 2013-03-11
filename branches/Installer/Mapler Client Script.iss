; Script generated by the Inno Setup Script Wizard.
; SEE THE DOCUMENTATION FOR DETAILS ON CREATING INNO SETUP SCRIPT FILES!

#define MyAppName "Mapler.me Client"
#define MyAppVersion "1.0.0.2"
#define MyAppPublisher "Mapler.me"
#define MyAppURL "http://www.mapler.me/"
#define MyAppExeName "Mapler Client.exe"

[Setup]
; NOTE: The value of AppId uniquely identifies this application.
; Do not use the same AppId value in installers for other applications.
; (To generate a new GUID, click Tools | Generate GUID inside the IDE.)
AppId={{279273DF-7BC8-4F21-BD05-EFA5824895D6}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
;AppVerName={#MyAppName} {#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
AppSupportURL={#MyAppURL}
AppUpdatesURL={#MyAppURL}
DefaultDirName={pf}\Mapler.me
DefaultGroupName={#MyAppName}
AllowNoIcons=yes
OutputDir=.\
OutputBaseFilename=setup
SetupIconFile=.\..\newlogo.ico
Compression=lzma
SolidCompression=yes

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked
Name: "quicklaunchicon"; Description: "{cm:CreateQuickLaunchIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked; OnlyBelowVersion: 0,6.1

[Files]
Source: ".\..\TestOmgeving\bin\x86\Release\Mapler Client.exe"; DestDir: "{app}"; Flags: ignoreversion
Source: ".\..\TestOmgeving\bin\x86\Release\AHA.dll"; DestDir: "{app}"; Flags: ignoreversion
Source: ".\..\TestOmgeving\bin\x86\Release\PacketDotNet.dll"; DestDir: "{app}"; Flags: ignoreversion
Source: ".\..\TestOmgeving\bin\x86\Release\SharpPcap.dll"; DestDir: "{app}"; Flags: ignoreversion
Source: ".\..\WinPcap Installer\WinPcap_4_1_2.exe"; DestDir: "{app}"; Flags: ignoreversion
; NOTE: Don't use "Flags: ignoreversion" on any shared system files

[Icons]
Name: "{group}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"
Name: "{group}\{cm:ProgramOnTheWeb,{#MyAppName}}"; Filename: "{#MyAppURL}"
Name: "{commondesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon
Name: "{userappdata}\Microsoft\Internet Explorer\Quick Launch\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: quicklaunchicon

[Run]          
Filename: "{app}\WinPcap_4_1_2.exe"; Flags: shellexec waituntilterminated
Filename: "{app}\{#MyAppExeName}"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Flags: nowait postinstall skipifsilent

