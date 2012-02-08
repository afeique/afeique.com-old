<p>
  The site source code is available on <?=a_link('http://github.com/','github')?> in 
  <?=a_link('http://github.com/afeique/afeique.com.git','this repository')?>.
</p>

<h2>Git on Windows 7</h2>
<p>
  On Windows 7, I currently use <?=a_link('http://code.google.com/p/msysgit/','msysGit')?>.
  To get started, download the installer and run. 
</p>

<p>
  On my machine, for "Adjusting your PATH Environment", I checked the "Use Git Bash only" radio.
  This is because I run <?=a_link('http://strawberryperl.com/','Strawberry Perl')?>. 
  Overriding the default Windows PATH will also override the default Windows Perl installation since msysGit comes 
  with Perl. I also run Python, Ruby, and Tcl interpreters on my Windows machine, but I can't attest
  to whether overriding the PATH with msysGit will affect their functionality.
</p>

<p>
  All my commits are made with Unix-style line endings. Be sure to keep that in mind and check the appropriate 
  "line-ending conversion" radio for checkout once you get to that screen.
</p>

