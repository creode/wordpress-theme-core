import * as fs from 'node:fs';

const HOT_RELOAD_FILE = 'hot-reload.json';

export function manageHotReloadFile(mode, hostname, port) {
	if('development' == mode) {
		fs.writeFile(
			HOT_RELOAD_FILE,
			JSON.stringify(
				{
					hostname: hostname,
					port: port
				}
			),
			{},
			(err) => {
				err && console.log(err);
			}
		);

		return;
	}

	if (!fs.existsSync(HOT_RELOAD_FILE)) {
		return;
	}

	fs.unlink(
		HOT_RELOAD_FILE,
		(err) => {
			err && console.log(err);
		}
	);
}