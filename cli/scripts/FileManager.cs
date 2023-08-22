namespace MythicalDash
{
    public class FileManager
    {
        public bool ConfigExists()
        {
            string filePath = "config.yml";

            if (File.Exists(filePath))
            {
                return true;
            } else {
                return false;
            }
        }

        public bool MFolderExists() 
        {
            string filePath = "migrate/info.md";
            if (File.Exists(filePath)) {
                return true;
            } else {
                return false;
            }
        }
    }
}