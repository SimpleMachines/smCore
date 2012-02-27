<tpl:container>
	<tpl:template name="site:main"><!DOCTYPE html>
		<html>
			<head>
				<title>Filters Demo</title>
				<style type="text/css">
					body {
						font: 10pt sans-serif;
						}
					h3 {
						}
					code {
						background: #eaeaea;
						border: 1px solid #555;
						display: block;
						width: 500px;
						max-height: 300px;
						overflow: auto;
						padding: 0.5em;
						font-family: "Consolas", monospace;
						}
					div.malicious {
						height: 100px;
						background: #fee;
						border: 1px solid red;
						color: #a33;
						font-weight: bold;
						padding: 0.2em;
						}
					.highlight {
						background: #feb;
						padding: 2px;
						border: 1px solid #fa0;
						border-radius: 4px;
						}
					pre {
						display: inline-block;
						}
				</style>
			</head>
			<body>
				<h3>contains</h3>
				<code>
					<tpl:if test="{$context.grades |contains(95)}">
						You did score a 95.<br />
					<tpl:else />
						You did not score a 95.<br />
					</tpl:if>

					<tpl:if test="{$context.alphabet |contains('apt')}">
						"apt" is in the alphabet.
					<tpl:else />
						"apt" is not in the alphabet.
					</tpl:if>
				</code>

				<h3>date</h3>
				<code>
					Default format: {$context.time |date}<br />
					Custom format: {$context.time |date("n/j/Y @ h:i:s A")}
				</code>

				<h3>default</h3>
				<code>
					This expression has a default value of "{$context.empty_string |default("N/A")}".
				</code>

				<h3>divisibleby</h3>
				<code>
					Is 9 divisible by 3?
					<tpl:if test="{$context.nine |divisibleby(3)}">
						Yes!<br />
					<tpl:else />
						No!<br />
					</tpl:if>

					Is 9 divisible by 4?
					<tpl:if test="{$context.nine |divisibleby(4)}">
						Yes!
					<tpl:else />
						No!
					</tpl:if>
				</code>

				<h3>empty</h3>
				<code>
					<tpl:if test="{$context.empty_array |empty}">
						There's nothing in an empty array.<br />
					<tpl:else />
						That's weird, there's something in an empty array...<br />
					</tpl:if>

					<tpl:if test="{$context.alphabet |empty}">
						The alphabet is empty...
					<tpl:else />
						The alphabet is not empty, thankfully.
					</tpl:if>
				</code>

				<h3>escape</h3>
				<code>
					<tpl:output value="{$context.malicious_js |escape}" escape="false" />
				</code>

				<h3>even</h3>
				<code>
					Ten is <tpl:if test="{$context.ten |even}">an even<tpl:else />not an even</tpl:if> number.<br />
					Nine is <tpl:if test="{$context.nine |even}">an even<tpl:else />not an even</tpl:if> number.
				</code>

				<h3>float</h3>
				<code>
					Pi: {$context.pi}<br />
					Filtered Pi: {$context.pi |float}<br />
					More Pi: {$context.pi |float(8)}
				</code>

				<h3>join</h3>
				<code>
					Your grades: {$context.grades |join(", ")}<br />
					Mash them together: {$context.grades |join}
				</code>

				<h3>json</h3>
				<code>
					{$context.huge_array |json}
				</code>

				<h3>length</h3>
				<code>
					"{$context.alphabet}" has {$context.alphabet |length} letters.<br />
					My big array has {$context.hundred_elements |length} items.
				</code>

				<h3>lower</h3>
				<code>
					{$context.name |lower}
				</code>

				<h3>ltrim</h3>
				<code>
					Original: <pre class="highlight">{$context.needs_trim}</pre><br />
					After trimming: <pre class="highlight">{$context.needs_trim |ltrim}</pre>
				</code>

				<h3>money</h3>
				<code>
				</code>

				<h3>nl2br</h3>
				<code>
					{$context.lorem_ipsum_2 |nl2br}
				</code>

				<h3>null</h3>
				<code>
					<tpl:if test="{$context.null_value |null}">
						A null value is null.
					<tpl:else />
						Something's broken here!
					</tpl:if>
				</code>

				<h3>odd</h3>
				<code>
					Nine is <tpl:if test="{$context.nine |odd}">an odd<tpl:else />not an odd</tpl:if> number.<br />
					Ten is <tpl:if test="{$context.ten |odd}">an odd<tpl:else />not an odd</tpl:if> number.
				</code>

				<h3>random</h3>
				<code>
					{$context.greetings |random}
				</code>

				<h3>raw</h3>
				<code>
					Value: {$context.html}<br />
					Raw: {$context.html |raw}
				</code>

				<h3>rtrim</h3>
				<code>
					Original: <pre class="highlight">{$context.needs_trim}</pre><br />
					After trimming: <pre class="highlight">{$context.needs_trim |rtrim}</pre>
				</code>

				<h3>stripchars</h3>
				<code>
				</code>

				<h3>striptags</h3>
				<code>
				</code>

				<h3>time</h3>
				<code>
					Default format: {$context.time |time}<br />
					Custom format: {$context.time |time("g:i A")}
				</code>

				<h3>trim</h3>
				<code>
					Original: <pre class="highlight">{$context.needs_trim}</pre><br />
					After trimming: <pre class="highlight">{$context.needs_trim |trim}</pre>
				</code>

				<h3>truncate</h3>
				<code>
				</code>

				<h3>truncatewords</h3>
				<code>
				</code>

				<h3>ucfirst</h3>
				<code>
					{$context.name_lower |ucfirst}
				</code>

				<h3>ucwords</h3>
				<code>
					{$context.name_lower |ucwords}
				</code>

				<h3>upper</h3>
				<code>
					{$context.name |upper}
				</code>

				<h3>urlencode</h3>
				<code>
				</code>

				<h3>wordcount</h3>
				<code>
					My lorem ipsum text has {$context.lorem_ipsum |wordcount} words.
				</code>

				<h3>wordwrap</h3>
				<code>
				</code>

				<h3>wrap</h3>
				<code>
				</code>
			</body>
		</html>
	</tpl:template>
</tpl:container>