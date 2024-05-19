# Important Document

Someone in Accounting received an important invoice from a customer but when they tried to open it they just saw a login page for Microsoft Excel. Unfortunately they already may have fallen for a common phishing scheme. Can you examine this document and try to see exactly what was exfiltrated?

## Solution

The page has a simple form which accepts input from the user and then submits it to a fake address, with an encrypted flag parameter attached. The original script was obfuscated with obfuscate.io and then converted to binary. After converting it back from binary, the player can use deobfuscat.io to provide a more readable version of the code to examine. Then, they will eventually find that the flag plaintext is written in base-64 in the source code which is then encrypted with AES before being submitted. 