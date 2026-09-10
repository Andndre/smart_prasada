import { execSync } from 'node:child_process'
import { readdirSync } from 'node:fs'
import { join } from 'node:path'

const srcDir = 'D:/smart_prasada/docs/diagrams/src'
const outDir = 'D:/smart_prasada/docs/diagrams'
const cliPath = 'C:/Users/Andndre/.agents/skills/drawio/scripts/cli.js'

const files = readdirSync(srcDir).filter(f => f.endsWith('.yaml'))

for (const file of files) {
  const baseName = file.replace('.yaml', '')
  const yamlPath = join(srcDir, file)
  const drawioPath = join(outDir, `${baseName}.drawio`)
  const svgPath = join(outDir, `${baseName}.svg`)

  console.log(`\nCompiling ${baseName}...`)
  try {
    const resDrawio = execSync(`node "${cliPath}" "${yamlPath}" "${drawioPath}" --validate`, { encoding: 'utf-8' })
    console.log(`[drawio] ${baseName}.drawio OK`)
    const resSvg = execSync(`node "${cliPath}" "${yamlPath}" "${svgPath}"`, { encoding: 'utf-8' })
    console.log(`[svg] ${baseName}.svg OK`)
  } catch (err) {
    console.error(`Error compiling ${baseName}:`, err.message)
    if (err.stdout) console.log('stdout:', err.stdout)
    if (err.stderr) console.error('stderr:', err.stderr)
  }
}

console.log('\nAll diagrams processed successfully!')
