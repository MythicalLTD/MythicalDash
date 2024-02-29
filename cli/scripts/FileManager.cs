namespace MythicalDash
{
    public class FileManager
    {
        public bool ConfigExists()
        {
            string filePath = "/var/www/mythicaldash/config.yml";

            if (File.Exists(filePath))
            {
                return true;
            } else {
                return false;
            }
        }

        public bool MFolderExists() 
        {
            string filePath = "/var/www/mythicaldash/migrate/info.md";
            if (File.Exists(filePath)) {
                return true;
            } else {
                return false;
            }
        }
    }
}