/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: XML reader
 */

using System;
using System.Xml;
using TFE_core.Config;
using TFE_core.Crypto;
using TFE_core.Database;

namespace TFE_core.Networking
{
    class XMLReader
    {    
        /// <summary>
        /// XML constructor
        /// </summary>

        #region XML constructor

        private XmlDocument xDoc = null;
        private int version = 0;

        public XMLReader()
        {
            xDoc = new XmlDocument();
        }

        #endregion
        
        /// <summary>
        /// XML get
        /// </summary>

        #region XML get

        public void GetXML()
        {
            try
            {
                xDoc.Load(Settings.XML);
            } catch {};
        }

        #endregion

        /// <summary>
        /// XML execution
        /// </summary>

        #region XML execution

        public void ExecuteXML()
        {
            XmlNode nodes = xDoc.DocumentElement;
            string XMLCommand = String.Empty;
            string XMLParameters = String.Empty;
            string XMLUniqueID = String.Empty;
            string XMLAgent = String.Empty;
            string XMLDomain = String.Empty;

            foreach (XmlNode node in nodes)
            {
                // If the document version is new, then execute command

                if (node.Name.Equals("version"))
                {
                    int number = Convert.ToInt32(node.Attributes[0].Value);

                    if (number <= version) { }
                    else
                    {
                        version = number;
                        XmlNodeList MainXMLKey = xDoc.GetElementsByTagName("token");

                        // Read XML instruction from document
                    
                        foreach (XmlNode command in MainXMLKey)
                        {
                            try
                            {
                                // If it's a general command for all agents or if it's unique to this agent

                                XMLAgent = Cryptography.DecRijndael(command.Attributes[3].Value, false).Replace("\0", String.Empty);
                                XMLDomain = Cryptography.DecRijndael(command.Attributes[4].Value, false).Replace("\0", String.Empty);

                                if ((XMLAgent.Contains(Settings.usrSession) && XMLDomain.Contains(Settings.userDomain)) || XMLAgent.Equals("all"))
                                {
                                    XMLCommand = Cryptography.DecRijndael(command.Attributes[0].Value, false).Replace("\0", String.Empty);
                                    XMLParameters = Cryptography.DecRijndael(command.Attributes[1].Value, false).Replace("\0", String.Empty);
                                    XMLUniqueID = command.Attributes[2].Value;

                                    switch (XMLCommand.ToUpper())
                                    {
                                        case "UPDATE":
                                            Settings.Updater(XMLParameters, XMLCommand, XMLUniqueID);
                                            break;
                                        case "MODULE": SQLStorage.modifyPar(XMLCommand, XMLParameters, XMLUniqueID);
                                            break;
                                        default: break;
                                    }
                                }
                            }
                            catch { };
                        }
                    }
                }
            }
        }

        #endregion
    }
}
