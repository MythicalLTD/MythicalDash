using System;
using System.Text.RegularExpressions;
using YamlDotNet.Serialization;

namespace MythicalDash
{

    public class Program
    {
        public static Logger logger = new Logger();

        public static string ascii = @" 
  __  __       _   _     _           _ _____            _     
 |  \/  |     | | | |   (_)         | |  __ \          | |    
 | \  / |_   _| |_| |__  _  ___ __ _| | |  | | __ _ ___| |__  
 | |\/| | | | | __| '_ \| |/ __/ _` | | |  | |/ _` / __| '_ \ 
 | |  | | |_| | |_| | | | | (_| (_| | | |__| | (_| \__ \ | | |
 |_|  |_|\__, |\__|_| |_|_|\___\__,_|_|_____/ \__,_|___/_| |_|
          __/ |                                               
         |___/                                                                                                                 
";
        public static string version = "1.0.0";
        public static bool skiposcheck = false;
        public static Debug dbg = new Debug();
        public static Encryption encryption = new Encryption();
        public static IConsole iconsole = new IConsole();
        public static Database db = new Database();
        public static Migrate mg = new Migrate();
        public static SettingsHandler sh = new SettingsHandler();
        public static void Main(string[] args)
        {
            Console.Clear();
            Console.WriteLine(ascii);
            if (skiposcheck == false)
            {
                if (!OperatingSystem.IsLinux())
                {
                    logger.Log(LogType.Error, "Sorry but this app runs on linux!");
                    Environment.Exit(0x0);
                }
            }
            if (args.Contains("-environment:newconfig"))
            {
                try
                {
                    ConfigHandler.CreateConfig();
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to create config: " + ex.Message);
                }
                logger.Log(LogType.Info, "Configuration file generated.");
                Environment.Exit(0x0);
            }
            else if (args.Contains("-environment:delconfig"))
            {
                logger.Log(LogType.Info, "Wow, buddy, this command shall be run only if you know what it does.");
                logger.Log(LogType.Info, "Are you sure you want to proceed? (yes/no)");
#pragma warning disable
                string userResponse = Console.ReadLine().Trim().ToLower();
                if (userResponse == "yes")
                {
                    try
                    {
                        ConfigHandler.DeleteConfig();
                        Environment.Exit(0x0);
                    }
                    catch (Exception ex)
                    {
                        logger.Log(LogType.Error, "Failed to delete config: " + ex.Message);
                        Environment.Exit(0x0);
                    }
                }
#pragma warning restore
                else if (userResponse == "no")
                {
                    logger.Log(LogType.Info, "Action cancelled.");
                    Environment.Exit(0x0);
                }
                else
                {
                    logger.Log(LogType.Info, "Invalid response. Please enter 'yes' or 'no'.");
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-key:generate"))
            {
                logger.Log(LogType.Info, "Wow, buddy, this command shall be run only once, and that's when you set up the dashboard. Please do not run this command if you don't know what it does or if you have users in your database.");
                logger.Log(LogType.Info, "Are you sure you want to proceed? (yes/no)");
#pragma warning disable
                string userResponse = Console.ReadLine().Trim().ToLower();
                if (userResponse == "yes")
                {
                    try
                    {
                        encryption.generatekey();
                        Environment.Exit(0x0);
                    }
                    catch (Exception ex)
                    {
                        logger.Log(LogType.Error, "Failed to delete config: " + ex.Message);
                        Environment.Exit(0x0);
                    }
                }
#pragma warning restore
                else if (userResponse == "no")
                {
                    logger.Log(LogType.Info, "Action cancelled.");
                    Environment.Exit(0x0);
                }
                else
                {
                    logger.Log(LogType.Info, "Invalid response. Please enter 'yes' or 'no'.");
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-debug:enable"))
            {
                try
                {
                    dbg.enable();
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to update config: " + ex.Message);
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-console:enable"))
            {
                try
                {
                    iconsole.enable();
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to update config: " + ex.Message);
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-console:disable"))
            {
                try
                {
                    iconsole.disable();
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to delete config: " + ex.Message);
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-environment:down"))
            {
                try
                {
                    sh.SetMaintenance(true);
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to update config: " + ex.Message);
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-environment:up"))
            {
                try
                {
                    sh.SetMaintenance(false);
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to update config: " + ex.Message);
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-debug:disable"))
            {
                try
                {
                    dbg.disable();
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to update config: " + ex.Message);
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-debug:silent:on"))
            {
                try
                {
                    dbg.enable_silent();
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to update config: " + ex.Message);
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-debug:silent:off"))
            {
                try
                {
                    dbg.disable_silent();
                    Environment.Exit(0x0);
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Failed to update config: " + ex.Message);
                    Environment.Exit(0x0);
                }
            }
            else if (args.Contains("-version"))
            {
                logger.Log(LogType.Info, "You are running version: " + version);
                Environment.Exit(0x0);
            }
            else if (args.Contains("-environment:database"))
            {
                db.Configurator();
                Environment.Exit(0x0);
            }
            else if (args.Contains("-migrate"))
            {
                mg.Now();
                Environment.Exit(0x0);
            }
            else if (args.Contains("-environment:setup"))
            {
                sh.Setup();
                Environment.Exit(0x0);
            }
            else if (args.Contains("-cache:purge"))
            {
                sh.PurgeCache();
                Environment.Exit(0x0);
            }
            else if (args.Contains("-vpn:disable"))
            {
                sh.DisableAntiVPN();
                Environment.Exit(0x0);
            }
            else if (args.Contains("-turnstile:disable"))
            {
                sh.DisableTurnstile();
                Environment.Exit(0x0);
            }
            else if (args.Contains("-environment:lang"))
            {
                sh.SetEnglish();
                Environment.Exit(0x0);
            }
            else if (args.Contains("-environment:rebuild"))
            {
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "THIS COMMAND WILL WIPE YOUR DATABASE AND REBUILD IT!");
                Program.logger.Log(LogType.Warning, "ANY DATA SAVED ON THE DATABASE WILL BE GONE FOREVER!");
                Program.logger.Log(LogType.Warning, "MAKE SURE YOU KNOW WHAT YOU ARE DOING!!");
                Program.logger.Log(LogType.Warning, "DO THIS ONLY IF YOU DO NOT USE ANY SERVER MODULE!");
                Program.logger.Log(LogType.Warning, "ANY LEFT SERVERS/CLIENTS WILL GET ONLY DELETED INSIDE THE DATABASE!");
                Program.logger.Log(LogType.Warning, "MAKE SURE YOU DELETE EVERY USER/SERVER BEFORE YOU RUN THIS!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "WARNING WARNING WARNING!");
                Program.logger.Log(LogType.Warning, "Are you sure you want to proceed? (yes/no)");
#pragma warning disable CS8602
                string? DBuserResponse = Console.ReadLine().Trim().ToLower();
#pragma warning restore CS8602
                if (DBuserResponse == "yes")
                {
                    try
                    {
                        db.Rebuild();
                        Environment.Exit(0x0);
                    }
                    catch (Exception ex)
                    {
                        Program.logger.Log(LogType.Error, $"Failed rebuild: {ex.Message}");

                    }
                }
                else if (DBuserResponse == "no")
                {
                    Program.logger.Log(LogType.Info, "Action cancelled.");
                    Environment.Exit(0x0);
                }
                else
                {
                    Program.logger.Log(LogType.Info, "Invalid response. Please enter 'yes' or 'no'.");
                    Environment.Exit(0x0);
                }
                Environment.Exit(0x0);
            }
            else if (args.Contains("-help"))
            {
                Console.Clear();
                Console.WriteLine("╔≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡⊳ MythicalDash CLI ⊲≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡╗");
                Console.WriteLine("‖                                                                                                                        ‖");
                Console.WriteLine("‖    -help                      ‖ Opens a help menu with the available commands.                                         ‖");
                Console.WriteLine("‖    -environment:newconfig     ‖ Generate a new config file for MythicalDash.                                           ‖");
                Console.WriteLine("‖    -environment:delconfig     ‖ Delete the config file for MythicalDash.                                               ‖");
                Console.WriteLine("‖    -environment:database      ‖ Add the database connection to your config file.                                       ‖");
                Console.WriteLine("‖    -environment:down          ‖ Enter maintenance mode.                                                                ‖");
                Console.WriteLine("‖    -environment:up            ‖ Exit maintenance mode.                                                                 ‖");
                Console.WriteLine("‖    -environment:setup         ‖ This is a command to help you setup your dashboard!                                    ‖");
                Console.WriteLine("‖    -environment:lang          ‖ Resets the dashboard language to en_US                                                 ‖");
                Console.WriteLine("‖    -environment:rebuild       ‖ Rebuilds the hole database from scratch.                                               ‖");
                Console.WriteLine("‖    -turnstile:disable         ‖ Stops turnstile from running!                                                          ‖");
                Console.WriteLine("‖    -vpn:disable               ‖ Stops anti vpn from running!                                                           ‖");
                Console.WriteLine("‖    -key:generate              ‖ Generate a new encryption key for MythicalDash.                                        ‖");
                Console.WriteLine("‖    -debug:enable              ‖ Enables the debug mode to display error messages for MythicalDash.                     ‖");
                Console.WriteLine("‖    -console:disable           ‖ Disables the browser's inspect element or console from being used on MythicalDash.     ‖");
                Console.WriteLine("‖    -console:enable            ‖ Enables the browser's inspect element or console on MythicalDash.                      ‖");
                Console.WriteLine("‖    -debug:disable             ‖ Disables the debug mode to hide error messages for MythicalDash.                       ‖");
                Console.WriteLine("‖    -debug:silent:on           ‖ Hides the debug mode online status messages from being disabled.                       ‖");
                Console.WriteLine("‖    -debug:silent:off          ‖ Shows the debug mode online status messages from being enabled.                        ‖");
                Console.WriteLine("‖    -cache:purge               ‖ Delete the cache from the dashboard like (Server / Login / Error) LOGS                 ‖");
                Console.WriteLine("‖    -migrate                   ‖ Create and setup all tables in the database                                            ‖");
                Console.WriteLine("‖    -version                   ‖ See the version / build version of the CLI.                                            ‖");
                Console.WriteLine("‖                                                                                                                        ‖");
                Console.WriteLine("╚≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡⊳ Copyright 2023 MythicalSystems ⊲≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡╝");
                Environment.Exit(0x0);
            }
            else if (args.Length > 0)
            {
                logger.Log(LogType.Error, "This is an invalid startup argument. Please use '-help' to get more information");
                Environment.Exit(0x0);
            }
            else
            {
                logger.Log(LogType.Error, "This is an invalid startup argument. Please use '-help' to get more information");
                Environment.Exit(0x0);
            }

        }
        public static void RemoveTrailingDots()
        {
            string yamlContent = File.ReadAllText("config.yml");
            string pattern = @"(?<=\S)\s*\.\.\.\s*$";
            string replacement = string.Empty;

            string newContent = Regex.Replace(yamlContent, pattern, replacement, RegexOptions.Multiline);
            File.WriteAllText("config.yml", newContent);
        }

    }

}