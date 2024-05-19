# Super Secret Adventure

This is a Gameboy reverse-engineering challenge that allows the player to play a Gameboy ROM in an emulator and explore the functionality needed to decrypt the flag. However, some reverse engineering of the ROM file itself using tools like Ghidra is required to discern the encryption algorithm used and how to recover it.


## Building


To build the ROM, you must have GBDK 2000 installed and set the GBDK_HOME variable in the Makefile, then run:


```bash
make
```

## Solution

This challenge uses a custom Gameboy ROM which is playable using an emulator. The player is dropped into a simple room with a book that they can attempt to read but it only shows a flag icon and a bunch of jumbled data. The wizard nearby informs them that he is also unable to decipher the book but says he has an old spell that gave him a single word as a clue. Then he gives the player a string of seemingly random characters. Then, the wizard asks the player for 8 hex characters for some unknown reason.

At this point, the player has a choice of how they would like to approach things. They can either use an emulator that allows for breakpoints and try to break at precisely the right spot to dynamically debug the game and find the values that the wizard would like, or they can open it up in Ghidra using a Gameboy plugin. Using the static path with Ghidra, the examination will eventually come to a function where the flag checking happens, and they may see an algorithm that they recognize (or just reconstruct manually) which is the RC4 encryption algorithm, using the key that the wizard gave. Further analysis shows that the answer he wants is the first four bytes of the key stream using the key he gave. 

Either way, if the player is able to provide these first four bytes, or just guesses at the algorithm being used and gets it that way, then the wizard will let them know they were successful and to check the book. The RC4 algorithm is used to decrypt the flag textures in the game which have been encrypted, and then become readable to get the flag from the book.