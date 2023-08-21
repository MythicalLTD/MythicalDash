using YamlDotNet.RepresentationModel;

namespace MythicalDash
{
    public class Encryption {
        FileManager fm = new FileManager();
        public void generatekey() {
            if (fm.Exist() == true)
            {
                string filePath = "config.yml"; 
                var yaml = new YamlStream();

                using (var reader = new StreamReader(filePath))
                {
                    yaml.Load(reader);
                }

                var mapping = (YamlMappingNode)yaml.Documents[0].RootNode;
                var appSection = (YamlMappingNode)mapping["app"];
                string key = KeyChecker.GenerateStrongKey();
                appSection.Children[new YamlScalarNode("encryptionkey")] = new YamlScalarNode(key); 

                using (var writer = new StreamWriter(filePath))
                {
                    yaml.Save(writer, false);
                } 
                Program.logger.Log(LogType.Info,"We updated the settings");
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        } 
    }
}