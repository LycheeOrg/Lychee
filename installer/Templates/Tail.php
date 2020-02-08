<?php


namespace Installer\Templates;


use Template;

class Tail implements Template
{

	public function print(array $input = [])
	{
		echo '
                </div>
            </div>
        </div>
    </body>
</html>';
	}
}